<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $user = $request->user();

        // Check if this is a buy-now order
        $buyNowData = session('buy_now');
        $isBuyNow = !empty($buyNowData) && isset($request->items[0]) && str_starts_with((string) $request->items[0], 'buy-now-');

        if ($isBuyNow) {
            return $this->createBuyNowOrder($request, $user, $buyNowData);
        }

        // Regular cart order processing
        $validated = $request->validate([
            'payment_method_code' => 'required|string|exists:payment_methods,code',
            'items' => 'required|array',
            'items.*' => 'integer|exists:cart_items,id',
            'address_id' => 'required|integer|exists:addresses,id',
            'shipping_selections' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);

        $paymentMethod = PaymentMethod::where('code', $validated['payment_method_code'])->firstOrFail();
        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cart = Cart::query()
            ->open()
            ->where('user_id', $user->id)
            ->with([
                'items' => function ($query) use ($validated) {
                    $query->whereIn('id', $validated['items']);
                },
                'items.product.store',
            ])
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Tidak ada item yang dipilih untuk checkout.');
        }

        $orderIds = [];

        DB::transaction(function () use ($cart, $user, $paymentMethod, $address, $validated, &$orderIds) {
            // Group items by store
            $itemsByStore = $cart->items->groupBy('store_id');

            foreach ($itemsByStore as $storeId => $items) {
                $store = $items->first()->product->store;

                // Calculate totals
                $subtotal = 0;
                $weightTotal = 0;

                foreach ($items as $item) {
                    $product = $item->product;
                    $price = $item->unit_price ?? $product->sale_price ?? $product->price;
                    $subtotal += $price * $item->quantity;
                    $weightTotal += ($product->weight ?? 0) * $item->quantity;
                }

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'store_id' => $storeId,
                    'address_id' => $address->id,
                    'payment_method_id' => $paymentMethod->id,
                    'order_number' => $this->generateOrderNumber(),
                    'order_type' => 'retail',
                    'status' => 'pending_payment',
                    'payment_status' => 'pending',
                    'payment_term' => 'immediate',
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'shipping_cost' => 0, // Will be calculated based on shipping selection
                    'weight_total' => $weightTotal,
                    'grand_total' => $subtotal,
                    'shipping_service' => $validated['shipping_selections'][$storeId] ?? null,
                    'ordered_at' => now(),
                    'expires_at' => now()->addDays(1), // 24 hours to complete payment
                    'note' => null,
                ]);

                // Create order items and reduce stock
                foreach ($items as $item) {
                    $product = $item->product;
                    $price = $item->unit_price ?? $product->sale_price ?? $product->price;
                    $itemType = $product->item_type === Product::ITEM_TYPE_SERVICE ? 'service' : 'product';

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'item_type' => $itemType,
                        'quantity' => $item->quantity,
                        'unit_price' => $price,
                        'subtotal' => $price * $item->quantity,
                        'weight' => $product->weight ?? 0,
                        'note' => $validated['notes'][$item->id] ?? null,
                    ]);

                    // Reduce stock
                    if ($product->stock !== null) {
                        $product->decrement('stock', $item->quantity);
                        $product->refresh();

                        // Send low stock notification if stock <= 10
                        if ($product->stock <= 10 && $product->stock >= 0 && $store && $store->user) {
                            $store->user->notify(new LowStockNotification(
                                $product->name,
                                $product->stock,
                                $product->id
                            ));
                        }
                    }
                }

                $orderIds[] = $order->id;

                // Send notification to seller
                if ($store && $store->user) {
                    $store->user->notify(new NewOrderNotification($order));
                }

                // Send notification to customer
                $user->notify(new OrderConfirmedNotification($order));

                // Delete cart items
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        });

        return redirect()->route('orders.confirmation', ['order_ids' => implode(',', $orderIds)])
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    protected function createBuyNowOrder(Request $request, $user, array $buyNowData)
    {
        $validated = $request->validate([
            'payment_method_code' => 'required|string|exists:payment_methods,code',
            'address_id' => 'required|integer|exists:addresses,id',
            'shipping_selections' => 'nullable|array',
        ]);

        $product = Product::with('store')->findOrFail($buyNowData['product_id']);
        $paymentMethod = PaymentMethod::where('code', $validated['payment_method_code'])->firstOrFail();
        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $price = $buyNowData['price'] ?? $product->sale_price ?? $product->price ?? 0;
        $quantity = $buyNowData['quantity'] ?? 1;
        $subtotal = $price * $quantity;
        $weightTotal = ($product->weight ?? 0) * $quantity;
        $store = $product->store;

        $orderId = null;

        DB::transaction(function () use ($user, $product, $store, $paymentMethod, $address, $validated, $price, $quantity, $subtotal, $weightTotal, &$orderId) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'store_id' => $store?->id,
                'address_id' => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'order_number' => $this->generateOrderNumber(),
                'order_type' => 'retail',
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'payment_term' => 'immediate',
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'shipping_cost' => 0,
                'weight_total' => $weightTotal,
                'grand_total' => $subtotal,
                'shipping_service' => $validated['shipping_selections'][$store?->id] ?? null,
                'ordered_at' => now(),
                'expires_at' => now()->addDays(1),
                'note' => null,
            ]);

            // Create order item
            $itemType = $product->item_type === Product::ITEM_TYPE_SERVICE ? 'service' : 'product';

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'item_type' => $itemType,
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $subtotal,
                'weight' => $product->weight ?? 0,
                'note' => null,
            ]);

            // Reduce stock
            if ($product->stock !== null) {
                $product->decrement('stock', $quantity);
                $product->refresh();

                // Send low stock notification if stock <= 10
                if ($product->stock <= 10 && $product->stock >= 0 && $store && $store->user) {
                    $store->user->notify(new LowStockNotification(
                        $product->name,
                        $product->stock,
                        $product->id
                    ));
                }
            }

            $orderId = $order->id;

            // Send notification to seller
            if ($store && $store->user) {
                $store->user->notify(new NewOrderNotification($order));
            }

            // Send notification to customer
            $user->notify(new OrderConfirmedNotification($order));
        });

        // Clear buy-now session data
        session()->forget('buy_now');

        return redirect()->route('orders.confirmation', ['order_ids' => $orderId])
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    public function confirmation(Request $request)
    {
        $orderIds = explode(',', $request->query('order_ids', ''));
        $user = $request->user();

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->where('user_id', $user->id)
            ->with(['store', 'paymentMethod', 'items.product', 'address'])
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        $orderData = $orders->map(function ($order) {
            $items = $order->items->map(function ($item) {
                return [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            })->values()->all();

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'store_id' => $order->store_id,
                'store_name' => $order->store->name,
                'store_phone' => $order->store->phone ?? null,
                'store_rating' => $order->store->rating ?? null,
                'store_response_time' => $order->store->response_time_label ?? null,
                'payment_method' => $order->paymentMethod->name,
                'payment_method_code' => $order->paymentMethod->code,
                'payment_instructions' => $order->paymentMethod->metadata['instructions'] ?? '',
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'shipping_cost' => $order->shipping_cost,
                'shipping_method' => $order->shipping_service,
                'grand_total' => $order->grand_total,
                'ordered_at' => $order->ordered_at?->format('d M Y H:i'),
                'expires_at' => $order->expires_at?->format('d M Y H:i'),
                'items_count' => $order->items->count(),
                'items' => $items,
                'whatsapp_link' => $order->getWhatsAppLink(),
            ];
        })->values()->all();

        return Inertia::render('OrderConfirmation', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'orders' => $orderData,
        ]);
    }

    protected function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        $sequence = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;

        return sprintf('ORD-%s-%04d', $date, $sequence);
    }
}
