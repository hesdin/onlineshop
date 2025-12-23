<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (! $store) {
            return Inertia::render('Seller/Dashboard/Index', [
                'store' => null,
                'needsStoreSetup' => true,
                'stats' => [],
                'recentOrders' => [],
                'topProducts' => [],
                'revenueChart' => [
                    'labels' => [],
                    'data' => [],
                ],
            ]);
        }

        $productCount = Product::where('store_id', $store->id)->count();

        $pendingOrdersCount = Order::where('store_id', $store->id)
            ->whereIn('status', ['pending_payment', 'processing', 'shipped'])
            ->count();

        $completedOrdersCount = Order::where('store_id', $store->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->count();

        // Current month revenue
        $monthlyRevenue = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('grand_total');

        // Last month revenue for growth calculation
        $lastMonthRevenue = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('grand_total');

        // Calculate growth percentages
        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($monthlyRevenue > 0 ? 100 : 0);

        // This week vs last week orders
        $thisWeekOrders = Order::where('store_id', $store->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $lastWeekOrders = Order::where('store_id', $store->id)
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();

        $ordersGrowth = $lastWeekOrders > 0
            ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1)
            : ($thisWeekOrders > 0 ? 100 : 0);

        // Products added this month vs last month
        $thisMonthProducts = Product::where('store_id', $store->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $lastMonthProducts = Product::where('store_id', $store->id)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();

        $productsGrowth = $lastMonthProducts > 0
            ? round((($thisMonthProducts - $lastMonthProducts) / $lastMonthProducts) * 100, 1)
            : ($thisMonthProducts > 0 ? 100 : 0);

        $recentOrders = Order::with('user:id,name,email')
            ->where('store_id', $store->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'grand_total' => $order->grand_total,
                'customer' => [
                    'name' => $order->user?->name,
                    'email' => $order->user?->email,
                ],
                'created_at' => $order->created_at?->toDateTimeString(),
            ]);

        $topProducts = OrderItem::selectRaw('product_id, name, SUM(quantity) as total_sold, SUM(subtotal) as total_amount')
            ->whereHas('order', fn ($query) => $query
                ->where('store_id', $store->id)
                ->whereIn('payment_status', ['paid']))
            ->groupBy('product_id', 'name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'name' => $item->name,
                'total_sold' => (int) $item->total_sold,
                'total_amount' => (int) $item->total_amount,
            ]);

        if ($topProducts->isEmpty()) {
            $topProducts = Product::where('store_id', $store->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Product $product) => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'total_sold' => 0,
                    'total_amount' => 0,
                    'image_url' => $product->image_url, // Ensure accessor exists or use default
                ]);
        }

        // Chart Data: Last 7 Days Revenue
        $endDate = now()->endOfDay();
        $startDate = now()->subDays(6)->startOfDay();

        $revenueData = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();
            $chartLabels[] = $date->locale('id')->dayName; // e.g., "Senin", "Selasa"
            $chartValues[] = $revenueData[$dateString] ?? 0;
        }

        // Additional Dashboard Data
        // Low stock products (stock <= 10)
        $lowStockProducts = Product::where('store_id', $store->id)
            ->where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'image_url' => $product->image_url ?? null,
            ]);

        // Out of stock products count
        $outOfStockCount = Product::where('store_id', $store->id)
            ->where('stock', 0)
            ->count();

        // Orders needing action (pending_payment or processing)
        $ordersNeedingAction = Order::with('user:id,name,email')
            ->where('store_id', $store->id)
            ->whereIn('status', ['pending_payment', 'processing'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'grand_total' => $order->grand_total,
                'customer_name' => $order->user?->name,
                'created_at' => $order->created_at?->diffForHumans(),
            ]);

        // Today's stats
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $todayOrders = Order::where('store_id', $store->id)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $todayRevenue = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('grand_total');

        // Total unique customers
        $totalCustomers = Order::where('store_id', $store->id)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Average order value (this month)
        $avgOrderValue = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->avg('grand_total') ?? 0;

        return Inertia::render('Seller/Dashboard/Index', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'city' => $store->city,
                'province' => $store->province,
                'tagline' => $store->tagline,
                'type' => $store->type,
                'tax_status' => $store->tax_status,
                'is_verified' => $store->is_verified,
                'response_time_label' => $store->response_time_label,
            ],
            'needsStoreSetup' => false,
            'stats' => [
                [
                    'label' => 'Produk aktif',
                    'value' => $productCount,
                    'helper' => 'Katalog toko',
                    'growth' => $productsGrowth,
                ],
                [
                    'label' => 'Pesanan berjalan',
                    'value' => $pendingOrdersCount,
                    'helper' => 'Menunggu diproses',
                    'growth' => $ordersGrowth,
                ],
                [
                    'label' => 'Pesanan selesai',
                    'value' => $completedOrdersCount,
                    'helper' => 'Sudah terkirim',
                    'growth' => $ordersGrowth,
                ],
                [
                    'label' => 'Pendapatan bulan ini',
                    'value' => $monthlyRevenue,
                    'helper' => 'Transaksi dibayar',
                    'growth' => $revenueGrowth,
                ],
            ],
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'revenueChart' => [
                'labels' => $chartLabels,
                'data' => $chartValues,
            ],
            // New data
            'lowStockProducts' => $lowStockProducts,
            'outOfStockCount' => $outOfStockCount,
            'ordersNeedingAction' => $ordersNeedingAction,
            'todayStats' => [
                'orders' => $todayOrders,
                'revenue' => $todayRevenue,
            ],
            'totalCustomers' => $totalCustomers,
            'avgOrderValue' => round($avgOrderValue),
            'growthData' => [
                'revenue' => $revenueGrowth,
                'orders' => $ordersGrowth,
                'products' => $productsGrowth,
            ],
        ]);
    }
}
