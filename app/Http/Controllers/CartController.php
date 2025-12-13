<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $cityId = CustomerLocationResolver::resolveCityId($request);

        $product = Product::query()
            ->select(['id', 'store_id', 'price', 'sale_price', 'stock', 'min_order', 'slug', 'visibility_scope', 'location_city_id'])
            ->with('store:id,city_id')
            ->findOrFail($data['product_id']);

        if (! $product->isVisibleForCity($cityId)) {
            return back()->with('error', 'Produk tidak tersedia untuk lokasi Anda.');
        }

        if (! $product->store_id) {
            return back()->with('error', 'Produk tidak tersedia untuk dipesan.');
        }

        if ($product->stock !== null && $product->stock <= 0) {
            return back()->with('error', 'Stok produk sedang habis.');
        }

        $minOrder = $product->min_order ?: 1;
        $requestedQuantity = max($data['quantity'], $minOrder);

        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $request->user()->id, 'status' => Cart::STATUS_OPEN],
            ['checked_out_at' => null]
        );

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $currentQty = $item->exists ? $item->quantity : 0;
        $newQty = $currentQty ? $currentQty + $requestedQuantity : $requestedQuantity;
        $newQty = max($newQty, $minOrder);

        if ($product->stock !== null && $newQty > $product->stock) {
            $newQty = $product->stock;

            if ($newQty <= $currentQty) {
                return back()->with('error', 'Jumlah melebihi stok tersedia.');
            }
        }

        $unitPrice = $product->sale_price ?? $product->price ?? 0;

        $item->fill([
            'store_id' => $product->store_id,
            'quantity' => $newQty,
            'unit_price' => $unitPrice,
            'subtotal' => $newQty * $unitPrice,
        ]);

        if (! empty($data['note'])) {
            $item->note = $data['note'];
        }

        $item->save();

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if (! $this->belongsToUser($request, $item)) {
            return back()->with('error', 'Item tidak ditemukan.');
        }

        $product = $item->product()
            ->select(['id', 'price', 'sale_price', 'stock', 'min_order'])
            ->first();

        if (! $product) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        $minOrder = $product->min_order ?: 1;
        $newQty = max($minOrder, $data['quantity']);

        if ($product->stock !== null) {
            $newQty = min($newQty, $product->stock);
        }

        $unitPrice = $item->unit_price ?? $product->sale_price ?? $product->price ?? 0;

        $item->update([
            'quantity' => $newQty,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $newQty,
        ]);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        if (! $this->belongsToUser($request, $item)) {
            return back()->with('error', 'Item tidak ditemukan.');
        }

        $item->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    protected function belongsToUser(Request $request, CartItem $item): bool
    {
        $cart = $item->cart()->select(['id', 'user_id', 'status'])->first();

        return $cart
            && $cart->user_id === $request->user()->id
            && $cart->status === Cart::STATUS_OPEN;
    }
}
