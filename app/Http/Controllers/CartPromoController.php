<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CartPromoController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'items' => 'nullable|array', // For cart flow
            'product_id' => 'nullable|integer', // For buy-now flow
            'quantity' => 'nullable|integer', // For buy-now flow
        ]);

        $code = $request->code;
        $user = $request->user();

        // 1. Find the promo code
        $promo = PromoCode::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Kode promo tidak ditemukan atau tidak aktif.',
            ], 422);
        }

        // 2. Check expiry
        $now = now();
        if ($promo->starts_at && $promo->starts_at->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'Promo ini belum dimulai.',
            ], 422);
        }

        if ($promo->ends_at && $promo->ends_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Promo ini sudah berakhir.',
            ], 422);
        }

        // 3. Check quota
        if ($promo->quota !== null && $promo->used >= $promo->quota) {
            return response()->json([
                'success' => false,
                'message' => 'Kuota promo sudah habis.',
            ], 422);
        }

        // 4. Get items subtotal for the promo's scope
        $itemsData = $this->getCartData($request, $user);
        $relevantSubtotal = 0;
        $totalSubtotal = 0;
        $appliesToStore = null;

        foreach ($itemsData as $item) {
            $totalSubtotal += $item['subtotal'];

            // If it's a platform promo (store_id is null) or matches the store
            if ($promo->store_id === null || $promo->store_id == $item['store_id']) {
                $relevantSubtotal += $item['subtotal'];
                if ($promo->store_id !== null) {
                    $appliesToStore = $promo->store_id;
                }
            }
        }

        if ($relevantSubtotal <= 0) {
            if ($promo->store_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo ini hanya berlaku untuk produk dari toko tertentu yang tidak ada di keranjang Anda.',
                ], 422);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang Anda kosong.',
                ], 422);
            }
        }

        // 5. Check minimum order amount
        if ($promo->min_order_amount && $relevantSubtotal < $promo->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal pembelian untuk promo ini adalah Rp ' . number_format($promo->min_order_amount, 0, ',', '.'),
            ], 422);
        }

        // 6. Calculate discount
        $discountAmount = 0;
        if ($promo->discount_type === 'percent') {
            $discountAmount = ($promo->discount_value / 100) * $relevantSubtotal;
            if ($promo->max_discount && $discountAmount > $promo->max_discount) {
                $discountAmount = $promo->max_discount;
            }
        } else {
            $discountAmount = $promo->discount_value;
        }

        // Ensure discount doesn't exceed the subtotal
        if ($discountAmount > $relevantSubtotal) {
            $discountAmount = $relevantSubtotal;
        }

        return response()->json([
            'success' => true,
            'promo' => [
                'id' => $promo->id,
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
                'discount_amount' => (int) $discountAmount,
                'store_id' => $promo->store_id,
            ],
            'message' => 'Promo berhasil diterapkan.',
        ]);
    }

    protected function getCartData(Request $request, $user)
    {
        // Buy-now flow
        if ($request->product_id) {
            $product = Product::find($request->product_id);
            if (!$product) return [];

            $price = $product->sale_price ?? $product->price;
            $quantity = $request->quantity ?? 1;

            return [[
                'store_id' => $product->store_id,
                'subtotal' => $price * $quantity,
            ]];
        }

        // Cart flow
        $selectedItems = collect(Arr::wrap($request->input('items')))
            ->filter()
            ->map(fn ($value) => (int) $value);

        if ($selectedItems->isEmpty()) return [];

        $cart = Cart::query()
            ->open()
            ->where('user_id', $user->id)
            ->with(['items' => function ($query) use ($selectedItems) {
                $query->whereIn('id', $selectedItems);
            }, 'items.product'])
            ->first();

        if (!$cart) return [];

        return $cart->items->map(function ($item) {
            $product = $item->product;
            $price = $item->unit_price ?? $product->sale_price ?? $product->price;
            return [
                'store_id' => $product->store_id,
                'subtotal' => $price * $item->quantity,
            ];
        });
    }
}
