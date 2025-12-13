<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PromoCodeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $promoCodes = PromoCode::query()
            ->when($search, fn ($query) => $query->where('code', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (PromoCode $promo) => [
                'id' => $promo->id,
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
                'start_date' => $promo->starts_at?->toDateTimeString(),
                'end_date' => $promo->ends_at?->toDateTimeString(),
                'is_active' => $promo->is_active,
                'usage' => "{$promo->used} / ".($promo->quota ?? '∞'),
            ]);

        return Inertia::render('Admin/PromoCodes/Index', [
            'promoCodes' => $promoCodes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/PromoCodes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:promo_codes,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        PromoCode::create($request->validated());

        return Redirect::route('admin.promo-codes.index')->with('success', 'Kode promo berhasil dibuat.');
    }

    public function edit(PromoCode $promoCode): Response
    {
        return Inertia::render('Admin/PromoCodes/Edit', [
            'promoCode' => $promoCode,
        ]);
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:promo_codes,code,'.$promoCode->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $promoCode->update($request->validated());

        return Redirect::route('admin.promo-codes.index')->with('success', 'Kode promo berhasil diperbarui.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();

        return Redirect::route('admin.promo-codes.index')->with('success', 'Kode promo berhasil dihapus.');
    }
}
