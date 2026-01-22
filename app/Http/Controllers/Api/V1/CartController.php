<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CartResource;
use App\Http\Resources\Api\V1\CartItemResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'status' => Cart::STATUS_OPEN,
            ]
        );

        $cart->load(['items.product', 'items.store']);

        return response()->json([
            'cart' => new CartResource($cart),
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
            'shipping_method' => ['nullable', 'in:pickup,delivery'],
        ]);

        $product = Product::with('store')->findOrFail($validated['product_id']);

        // Check product status
        if ($product->status === Product::STATUS_INACTIVE) {
            return response()->json([
                'message' => 'Produk tidak tersedia.',
            ], 422);
        }

        // Check stock
        if ($product->stock < $validated['quantity']) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi.',
                'available_stock' => $product->stock,
            ], 422);
        }

        // Check minimum order
        if ($validated['quantity'] < $product->min_order) {
            return response()->json([
                'message' => "Minimal pembelian adalah {$product->min_order} item.",
            ], 422);
        }

        // Get or create cart
        $cart = Cart::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'status' => Cart::STATUS_OPEN,
            ]
        );

        // Check if product already in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $unitPrice = $product->sale_price ?? $product->price;

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem->quantity + $validated['quantity'];

            if ($newQuantity > $product->stock) {
                return response()->json([
                    'message' => 'Stok produk tidak mencukupi.',
                    'available_stock' => $product->stock,
                    'current_in_cart' => $existingItem->quantity,
                ], 422);
            }

            $existingItem->update([
                'quantity' => $newQuantity,
                'subtotal' => $unitPrice * $newQuantity,
            ]);

            $item = $existingItem->fresh()->load(['product', 'store']);
        } else {
            // Create new item
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'store_id' => $product->store_id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $validated['quantity'],
                'note' => $validated['note'] ?? null,
                'shipping_method' => $validated['shipping_method'] ?? 'delivery',
            ]);

            $item->load(['product', 'store']);
        }

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'item' => new CartItemResource($item),
        ], 201);
    }

    /**
     * Update cart item
     */
    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'note' => ['sometimes', 'nullable', 'string', 'max:500'],
            'shipping_method' => ['sometimes', 'in:pickup,delivery'],
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->where('status', Cart::STATUS_OPEN)
            ->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->with('product')
            ->firstOrFail();

        // Check stock if quantity is being updated
        if (isset($validated['quantity'])) {
            if ($item->product->stock < $validated['quantity']) {
                return response()->json([
                    'message' => 'Stok produk tidak mencukupi.',
                    'available_stock' => $item->product->stock,
                ], 422);
            }

            if ($validated['quantity'] < $item->product->min_order) {
                return response()->json([
                    'message' => "Minimal pembelian adalah {$item->product->min_order} item.",
                ], 422);
            }

            $validated['subtotal'] = $item->unit_price * $validated['quantity'];
        }

        $item->update($validated);

        return response()->json([
            'message' => 'Item keranjang berhasil diperbarui.',
            'item' => new CartItemResource($item->fresh()->load(['product', 'store'])),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->where('status', Cart::STATUS_OPEN)
            ->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'message' => 'Item berhasil dihapus dari keranjang.',
        ]);
    }
}
