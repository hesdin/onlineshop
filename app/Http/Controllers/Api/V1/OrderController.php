<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderListResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * List user orders
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::where('user_id', $request->user()->id)
            ->with(['store', 'items.product'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $perPage = min($request->get('per_page', 20), 50);
        $orders = $query->paginate($perPage);

        return OrderListResource::collection($orders);
    }

    /**
     * Create order (checkout)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'cart_item_ids' => ['required', 'array', 'min:1'],
            'cart_item_ids.*' => ['integer', 'exists:cart_items,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify address belongs to user
        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Get cart
        $cart = Cart::where('user_id', $request->user()->id)
            ->where('status', Cart::STATUS_OPEN)
            ->firstOrFail();

        // Get cart items
        $cartItems = $cart->items()
            ->whereIn('id', $validated['cart_item_ids'])
            ->with(['product', 'store'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Item keranjang tidak ditemukan.',
            ], 422);
        }

        // Get payment method
        $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);

        // Group cart items by store
        $groupedByStore = $cartItems->groupBy('store_id');

        $orders = [];

        DB::beginTransaction();

        try {
            foreach ($groupedByStore as $storeId => $storeItems) {
                $subtotal = $storeItems->sum('subtotal');
                $weightTotal = $storeItems->sum(fn($item) => ($item->product->weight ?? 0) * $item->quantity);

                // Create order
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'store_id' => $storeId,
                    'address_id' => $address->id,
                    'payment_method_id' => $paymentMethod->id,
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)) . '-' . time(),
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'shipping_cost' => 0,
                    'weight_total' => $weightTotal,
                    'grand_total' => $subtotal,
                    'note' => $validated['note'] ?? null,
                    'ordered_at' => now(),
                    'expires_at' => now()->addHours(24),
                ]);

                // Create order items
                foreach ($storeItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'name' => $cartItem->product->name,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->unit_price,
                        'subtotal' => $cartItem->subtotal,
                    ]);

                    // Reduce stock
                    $cartItem->product->decrement('stock', $cartItem->quantity);

                    // Remove from cart
                    $cartItem->delete();
                }

                $orders[] = $order;
            }

            // If cart is empty after checkout, mark as converted
            if ($cart->items()->count() === 0) {
                $cart->update([
                    'status' => Cart::STATUS_CONVERTED,
                    'checked_out_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'orders' => OrderResource::collection(
                    collect($orders)->map(fn($o) => $o->load(['store', 'items', 'address', 'paymentMethod']))
                ),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal membuat pesanan. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get order detail
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Ensure order belongs to user
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        $order->load(['store', 'items.product', 'address', 'paymentMethod']);

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }
}
