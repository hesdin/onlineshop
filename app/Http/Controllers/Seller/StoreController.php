<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerStoreRequest;
use App\Models\Store;
use App\Models\User;
use App\Notifications\SellerDocumentSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function edit(Request $request): Response
    {
        $store = Store::where('user_id', $request->user()->id)->with('sellerDocument')->first();
        $sellerDocument = $store?->sellerDocument;

        return Inertia::render('Seller/Settings/Edit', [
            'store' => $store ? $this->formatStore($store) : $this->defaultStorePayload(),
            'hasStore' => (bool) $store,
            'typeOptions' => $this->typeOptions(),
            'taxStatusOptions' => $this->taxStatusOptions(),
            'sellerDocument' => $sellerDocument ? [
                'id' => $sellerDocument->id,
                'ktp_status' => $sellerDocument->ktp_status,
                'npwp_status' => $sellerDocument->npwp_status,
                'nib_status' => $sellerDocument->nib_status,
                'submission_status' => $sellerDocument->submission_status,
                'admin_notes' => $sellerDocument->admin_notes,
                'ktp_url' => $sellerDocument->ktp_url,
                'npwp_url' => $sellerDocument->npwp_url,
                'nib_url' => $sellerDocument->nib_url,
                'company_statement_url' => $sellerDocument->company_statement_url,
                'supporting_documents_urls' => $sellerDocument->supporting_documents_urls,
            ] : null,
        ]);
    }

    public function store(SellerStoreRequest $request): RedirectResponse
    {
        $existingStore = Store::where('user_id', $request->user()->id)->first();
        if ($existingStore) {
            return Redirect::route('seller.settings.edit')->with('info', 'Toko sudah tersedia, silakan perbarui.');
        }

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['is_verified'] = false;
        $data['transactions_count'] = $data['transactions_count'] ?? 0;

        $store = Store::create($data);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $store->addMediaFromRequest('logo')->toMediaCollection('store_logo');
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $store->addMediaFromRequest('banner')->toMediaCollection('store_banner');
        }

        return Redirect::route('seller.settings.edit')->with('success', 'Profil toko berhasil dibuat.');
    }

    public function update(SellerStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->first();

        $data = $request->validated();

        if (! $store) {
            $data['user_id'] = $user->id;
            $data['is_verified'] = false;
            $data['transactions_count'] = $data['transactions_count'] ?? 0;

            $store = Store::create($data);
        } else {
            $store->update($data);
        }

        // Update user phone
        if (!empty($data['phone'])) {
            $user->update(['phone' => $data['phone']]);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $store->clearMediaCollection('store_logo');
            $store->addMediaFromRequest('logo')->toMediaCollection('store_logo');
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $store->clearMediaCollection('store_banner');
            $store->addMediaFromRequest('banner')->toMediaCollection('store_banner');
        }

        // Handle document uploads
        $sellerDocument = $store->sellerDocument ?: $store->sellerDocument()->create(['submission_status' => 'draft']);

        if ($request->hasFile('ktp')) {
            $sellerDocument->addMedia($request->file('ktp'))->toMediaCollection('ktp');
            $sellerDocument->update(['ktp_status' => 'pending']);
        }

        if ($request->hasFile('npwp')) {
            $sellerDocument->addMedia($request->file('npwp'))->toMediaCollection('npwp');
            $sellerDocument->update(['npwp_status' => 'pending']);
        }

        if ($request->hasFile('nib')) {
            $sellerDocument->addMedia($request->file('nib'))->toMediaCollection('nib');
            $sellerDocument->update(['nib_status' => 'pending']);
        }

        if ($request->hasFile('company_statement')) {
            $sellerDocument->addMedia($request->file('company_statement'))->toMediaCollection('company_statement');
        }

        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                $sellerDocument->addMedia($file)->toMediaCollection('supporting_documents');
            }
        }

        return Redirect::route('seller.settings.edit')->with('success', 'Data toko berhasil disimpan.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument ?: $store->sellerDocument()->create(['submission_status' => 'draft']);
        $sellerDocument->refresh();

        // Validate required fields
        if (! $store->name || ! $store->type) {
            return back()->with('error', 'Silakan lengkapi nama toko dan jenis toko.');
        }

        if (!$store->logo_url) {
            return back()->with('error', 'Silakan upload logo toko.');
        }

        if (!$store->banner_url) {
            return back()->with('error', 'Silakan upload banner toko.');
        }

        if (!$sellerDocument->getFirstMedia('ktp')) {
            return back()->with('error', 'Silakan upload KTP Pemilik Usaha.');
        }

        if (!$sellerDocument->getFirstMedia('npwp')) {
            return back()->with('error', 'Silakan upload NPWP.');
        }

        if (!$sellerDocument->getFirstMedia('nib')) {
            return back()->with('error', 'Silakan upload NIB (Nomor Induk Berusaha).');
        }

        $sellerDocument->update([
            'submission_status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Notify all admins about new document submission
        $admins = User::role('superadmin')->get();
        Notification::send($admins, new SellerDocumentSubmittedNotification($store));

        return back()->with('success', 'Data berhasil disubmit untuk diverifikasi. Tim kami akan meninjau dalam 1-3 hari kerja.');
    }

    public function deleteSupportingDocument(Request $request, int $mediaId): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument;

        if ($sellerDocument) {
            $media = $sellerDocument->getMedia('supporting_documents')->firstWhere('id', $mediaId);
            if ($media) {
                $media->delete();
            }
        }

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    private function formatStore(Store $store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'phone' => $store->phone,
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
            'is_umkm' => $store->is_umkm,
            'response_time_label' => $store->response_time_label,
            'bank_name' => $store->bank_name,
            'bank_account_number' => $store->bank_account_number,
            'bank_account_name' => $store->bank_account_name,
            'logo_url' => $store->logo_url,
            'banner_url' => $store->banner_url,
        ];
    }

    private function defaultStorePayload(): array
    {
        return [
            'name' => '',
            'slug' => '',
            'phone' => '',
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
            'is_umkm' => true,
            'response_time_label' => '',
            'bank_name' => '',
            'bank_account_number' => '',
            'bank_account_name' => '',
            'logo_url' => null,
            'banner_url' => null,
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
