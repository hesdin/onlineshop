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
            ]);
        }

        $productCount = Product::where('store_id', $store->id)->count();

        $pendingOrdersCount = Order::where('store_id', $store->id)
            ->whereIn('status', ['pending_payment', 'processing', 'shipped'])
            ->count();

        $completedOrdersCount = Order::where('store_id', $store->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->count();

        $monthlyRevenue = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('grand_total');

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
                ]);
        }

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
                ],
                [
                    'label' => 'Pesanan berjalan',
                    'value' => $pendingOrdersCount,
                    'helper' => 'Menunggu diproses',
                ],
                [
                    'label' => 'Pesanan selesai',
                    'value' => $completedOrdersCount,
                    'helper' => 'Sudah terkirim',
                ],
                [
                    'label' => 'Pendapatan bulan ini',
                    'value' => $monthlyRevenue,
                    'helper' => 'Transaksi dibayar',
                ],
            ],
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ]);
    }
}
