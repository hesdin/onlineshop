<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerStoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function edit(Request $request): Response
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        return Inertia::render('Seller/Store/Edit', [
            'store' => $store ? $this->formatStore($store) : $this->defaultStorePayload(),
            'hasStore' => (bool) $store,
            'typeOptions' => $this->typeOptions(),
            'taxStatusOptions' => $this->taxStatusOptions(),
        ]);
    }

    public function store(SellerStoreRequest $request): RedirectResponse
    {
        $existingStore = Store::where('user_id', $request->user()->id)->first();
        if ($existingStore) {
            return Redirect::route('seller.store.edit')->with('info', 'Toko sudah tersedia, silakan perbarui.');
        }

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['is_verified'] = false;
        $data['transactions_count'] = $data['transactions_count'] ?? 0;

        Store::create($data);

        return Redirect::route('seller.store.edit')->with('success', 'Profil toko berhasil dibuat.');
    }

    public function update(SellerStoreRequest $request): RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        $data = $request->validated();

        if (! $store) {
            $data['user_id'] = $request->user()->id;
            $data['is_verified'] = false;
            $data['transactions_count'] = $data['transactions_count'] ?? 0;

            Store::create($data);

            return Redirect::route('seller.store.edit')->with('success', 'Profil toko berhasil dibuat.');
        }

        $store->update($data);

        return Redirect::route('seller.store.edit')->with('success', 'Profil toko berhasil diperbarui.');
    }

    private function formatStore(Store $store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'tagline' => $store->tagline,
            'description' => $store->description,
            'type' => $store->type,
            'tax_status' => $store->tax_status,
            'bumn_partner' => $store->bumn_partner,
            'city' => $store->city,
            'province' => $store->province,
            'district' => $store->district,
            'province_id' => $store->province_id,
            'city_id' => $store->city_id,
            'district_id' => $store->district_id,
            'postal_code' => $store->postal_code,
            'address_line' => $store->address_line,
            'is_verified' => $store->is_verified,
            'is_umkm' => $store->is_umkm,
            'response_time_label' => $store->response_time_label,
        ];
    }

    private function defaultStorePayload(): array
    {
        return [
            'name' => '',
            'slug' => '',
            'tagline' => '',
            'description' => '',
            'type' => 'umkm',
            'tax_status' => 'non_pkp',
            'bumn_partner' => '',
            'province_id' => null,
            'city_id' => null,
            'district_id' => null,
            'postal_code' => '',
            'address_line' => '',
            'is_verified' => false,
            'is_umkm' => true,
            'response_time_label' => '',
        ];
    }

    private function typeOptions(): array
    {
        return [
            ['value' => 'umkm', 'label' => 'UMKM'],
            ['value' => 'vendor', 'label' => 'Vendor'],
            ['value' => 'koperasi', 'label' => 'Koperasi'],
            ['value' => 'premium', 'label' => 'Premium'],
        ];
    }

    private function taxStatusOptions(): array
    {
        return [
            ['value' => 'pkp', 'label' => 'PKP'],
            ['value' => 'non_pkp', 'label' => 'Non PKP'],
        ];
    }
}
