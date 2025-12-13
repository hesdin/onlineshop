<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'payment_method_code' => 'required|string|exists:payment_methods,code',
            'items' => 'required|array',
            'items.*' => 'integer|exists:cart_items,id',
            'address_id' => 'required|integer|exists:addresses,id',
            'shipping_selections' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);

        $user = $request->user();
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

                // Create order items
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
                }

                $orderIds[] = $order->id;

                // Delete cart items
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        });

        return redirect()->route('orders.confirmation', ['order_ids' => implode(',', $orderIds)])
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
