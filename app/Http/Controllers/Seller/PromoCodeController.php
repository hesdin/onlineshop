<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PromoCodeController extends Controller
{
    public function index(Request $request): Response
    {
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();
        $search = $request->string('search')->toString();

        $promoCodes = PromoCode::query()
            ->where('store_id', $store->id)
            ->when($search, fn ($query) => $query->where('code', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (PromoCode $promo) => [
                'id' => $promo->id,
                'code' => $promo->code,
                'description' => $promo->description,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
                'starts_at' => $promo->starts_at?->toDateTimeString(),
                'ends_at' => $promo->ends_at?->toDateTimeString(),
                'is_active' => $promo->is_active,
                'usage' => "{$promo->used} / ".($promo->quota ?? '∞'),
            ]);

        return Inertia::render('Seller/PromoCodes/Index', [
            'promoCodes' => $promoCodes,
            'filters' => [
                'search' => $search,
            ],
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Seller/PromoCodes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:promo_codes,code',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'quota' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['store_id'] = $store->id;

        PromoCode::create($validated);

        return Redirect::route('seller.products.index')->with('success', 'Kode promo berhasil dibuat.');
    }

    public function edit(Request $request, PromoCode $promoCode): Response
    {
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        if ($promoCode->store_id !== $store->id) {
            abort(403);
        }

        return Inertia::render('Seller/PromoCodes/Edit', [
            'promoCode' => $promoCode,
        ]);
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        if ($promoCode->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:promo_codes,code,'.$promoCode->id,
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'quota' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $promoCode->update($validated);

        return Redirect::route('seller.products.index')->with('success', 'Kode promo berhasil diperbarui.');
    }

    public function destroy(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->firstOrFail();

        if ($promoCode->store_id !== $store->id) {
            abort(403);
        }

        $promoCode->delete();

        return Redirect::route('seller.products.index')->with('success', 'Kode promo berhasil dihapus.');
    }
}
