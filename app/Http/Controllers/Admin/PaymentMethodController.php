<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodController extends Controller
{
    public function index(Request $request): Response
    {
        $paymentMethods = PaymentMethod::query()
            ->orderBy('name')
            ->get()
            ->map(fn (PaymentMethod $method) => [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'is_active' => $method->is_active,
                'logo_url' => $method->getFirstMediaUrl('logo'),
            ]);

        return Inertia::render('Admin/PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/PaymentMethods/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        $method = PaymentMethod::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->hasFile('logo')) {
            $method->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return Redirect::route('admin.payment-methods.index')->with('success', 'Metode pembayaran berhasil dibuat.');
    }

    public function edit(PaymentMethod $paymentMethod): Response
    {
        return Inertia::render('Admin/PaymentMethods/Edit', [
            'paymentMethod' => [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name,
                'code' => $paymentMethod->code,
                'description' => $paymentMethod->description,
                'is_active' => $paymentMethod->is_active,
                'logo_url' => $paymentMethod->getFirstMediaUrl('logo'),
            ],
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code,'.$paymentMethod->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        $paymentMethod->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('logo')) {
            $paymentMethod->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return Redirect::route('admin.payment-methods.index')->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return Redirect::route('admin.payment-methods.index')->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}
