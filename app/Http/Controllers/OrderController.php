<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Events\NotificationReceived;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
            'agreement_accepted' => 'required|accepted',
            'agreement_accepted_at' => 'nullable|date',
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
        $ordersForNotification = [];
        $lowStockNotifications = [];
        $agreementAcceptedAt = $validated['agreement_accepted_at'] ?? now();

        DB::transaction(function () use ($cart, $user, $paymentMethod, $address, $validated, &$orderIds, &$ordersForNotification, &$lowStockNotifications, $agreementAcceptedAt) {
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
                    'agreement_accepted_at' => $agreementAcceptedAt,
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
                            $lowStockNotifications[] = [
                                'user' => $store->user,
                                'product_name' => $product->name,
                                'stock' => $product->stock,
                                'product_id' => $product->id,
                            ];
                        }
                    }
                }

                $orderIds[] = $order->id;

                // Collect orders for notification (will be sent after transaction)
                $ordersForNotification[] = [
                    'order' => $order,
                    'store' => $store,
                ];

                // Delete cart items
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        });

        // Send notifications AFTER transaction commits (for realtime to work)
        foreach ($ordersForNotification as $data) {
            $order = $data['order'];
            $store = $data['store'];

            // Reload order to get fresh data after commit
            $order->refresh();

            // Send notification to seller
            if ($store && $store->user) {
                Notification::sendNow($store->user, new NewOrderNotification($order));
                // Dispatch realtime event for seller
                $sellerNotification = $store->user->notifications()->latest()->first();
                if ($sellerNotification) {
                    event(new NotificationReceived($store->user, [
                        'id' => $sellerNotification->id,
                        'type' => class_basename($sellerNotification->type),
                        'title' => $sellerNotification->data['title'] ?? 'Notifikasi',
                        'message' => $sellerNotification->data['message'] ?? '',
                        'icon' => $sellerNotification->data['icon'] ?? 'bell',
                        'action_url' => $sellerNotification->data['action_url'] ?? null,
                        'read_at' => null,
                        'created_at' => $sellerNotification->created_at->diffForHumans(),
                    ]));
                }
            }

            // Send notification to customer
            Notification::sendNow($user, new OrderConfirmedNotification($order));
            // Dispatch realtime event for customer
            $customerNotification = $user->notifications()->latest()->first();
            if ($customerNotification) {
                event(new NotificationReceived($user, [
                    'id' => $customerNotification->id,
                    'type' => class_basename($customerNotification->type),
                    'title' => $customerNotification->data['title'] ?? 'Notifikasi',
                    'message' => $customerNotification->data['message'] ?? '',
                    'icon' => $customerNotification->data['icon'] ?? 'bell',
                    'action_url' => $customerNotification->data['action_url'] ?? null,
                    'read_at' => null,
                    'created_at' => $customerNotification->created_at->diffForHumans(),
                ]));
            }
        }

        // Send low stock notifications AFTER transaction commits
        foreach ($lowStockNotifications as $data) {
            $sellerUser = $data['user'];
            Notification::sendNow($sellerUser, new LowStockNotification(
                $data['product_name'],
                $data['stock'],
                $data['product_id']
            ));

            // Dispatch realtime event for seller
            $latestNotification = $sellerUser->notifications()->latest()->first();
            if ($latestNotification) {
                event(new NotificationReceived($sellerUser, [
                    'id' => $latestNotification->id,
                    'type' => class_basename($latestNotification->type),
                    'title' => $latestNotification->data['title'] ?? 'Notifikasi',
                    'message' => $latestNotification->data['message'] ?? '',
                    'icon' => $latestNotification->data['icon'] ?? 'bell',
                    'action_url' => $latestNotification->data['action_url'] ?? null,
                    'read_at' => null,
                    'created_at' => $latestNotification->created_at->diffForHumans(),
                ]));
            }
        }

        return redirect()->route('orders.confirmation', ['order_ids' => implode(',', $orderIds)])
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    protected function createBuyNowOrder(Request $request, $user, array $buyNowData)
    {
        $validated = $request->validate([
            'payment_method_code' => 'required|string|exists:payment_methods,code',
            'address_id' => 'required|integer|exists:addresses,id',
            'shipping_selections' => 'nullable|array',
            'agreement_accepted' => 'required|accepted',
            'agreement_accepted_at' => 'nullable|date',
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
        $orderForNotification = null;
        $lowStockData = null;
        $agreementAcceptedAt = $validated['agreement_accepted_at'] ?? now();

        DB::transaction(function () use ($user, $product, $store, $paymentMethod, $address, $validated, $price, $quantity, $subtotal, $weightTotal, &$orderId, &$orderForNotification, &$lowStockData, $agreementAcceptedAt) {
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
                'agreement_accepted_at' => $agreementAcceptedAt,
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

                // Collect low stock notification data if stock <= 10
                if ($product->stock <= 10 && $product->stock >= 0 && $store && $store->user) {
                    $lowStockData = [
                        'user' => $store->user,
                        'product_name' => $product->name,
                        'stock' => $product->stock,
                        'product_id' => $product->id,
                    ];
                }
            }

            $orderId = $order->id;
            $orderForNotification = $order;
        });

        // Send notifications AFTER transaction commits (for realtime to work)
        if ($orderForNotification) {
            $orderForNotification->refresh();

            // Send notification to seller
            if ($store && $store->user) {
                Notification::sendNow($store->user, new NewOrderNotification($orderForNotification));
                // Dispatch realtime event for seller
                $sellerNotification = $store->user->notifications()->latest()->first();
                if ($sellerNotification) {
                    event(new NotificationReceived($store->user, [
                        'id' => $sellerNotification->id,
                        'type' => class_basename($sellerNotification->type),
                        'title' => $sellerNotification->data['title'] ?? 'Notifikasi',
                        'message' => $sellerNotification->data['message'] ?? '',
                        'icon' => $sellerNotification->data['icon'] ?? 'bell',
                        'action_url' => $sellerNotification->data['action_url'] ?? null,
                        'read_at' => null,
                        'created_at' => $sellerNotification->created_at->diffForHumans(),
                    ]));
                }
            }

            // Send notification to customer
            Notification::sendNow($user, new OrderConfirmedNotification($orderForNotification));
            // Dispatch realtime event for customer
            $customerNotification = $user->notifications()->latest()->first();
            if ($customerNotification) {
                event(new NotificationReceived($user, [
                    'id' => $customerNotification->id,
                    'type' => class_basename($customerNotification->type),
                    'title' => $customerNotification->data['title'] ?? 'Notifikasi',
                    'message' => $customerNotification->data['message'] ?? '',
                    'icon' => $customerNotification->data['icon'] ?? 'bell',
                    'action_url' => $customerNotification->data['action_url'] ?? null,
                    'read_at' => null,
                    'created_at' => $customerNotification->created_at->diffForHumans(),
                ]));
            }
        }

        // Send low stock notification AFTER transaction commits
        if ($lowStockData) {
            $sellerUser = $lowStockData['user'];
            Notification::sendNow($sellerUser, new LowStockNotification(
                $lowStockData['product_name'],
                $lowStockData['stock'],
                $lowStockData['product_id']
            ));

            // Dispatch realtime event for seller
            $latestNotification = $sellerUser->notifications()->latest()->first();
            if ($latestNotification) {
                event(new NotificationReceived($sellerUser, [
                    'id' => $latestNotification->id,
                    'type' => class_basename($latestNotification->type),
                    'title' => $latestNotification->data['title'] ?? 'Notifikasi',
                    'message' => $latestNotification->data['message'] ?? '',
                    'icon' => $latestNotification->data['icon'] ?? 'bell',
                    'action_url' => $latestNotification->data['action_url'] ?? null,
                    'read_at' => null,
                    'created_at' => $latestNotification->created_at->diffForHumans(),
                ]));
            }
        }

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
