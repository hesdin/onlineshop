<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Notifications\SellerDocumentSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SellerDocumentController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument;

        return Inertia::render('Seller/Documents/Index', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'phone' => $store->phone,
                'tagline' => $store->tagline,
                'description' => $store->description,
                'type' => $store->type,
                'tax_status' => $store->tax_status,
                'bumn_partner' => $store->bumn_partner,
                'province_id' => $store->province_id,
                'city_id' => $store->city_id,
                'district_id' => $store->district_id,
                'province' => $store->province,
                'city' => $store->city,
                'district' => $store->district,
                'postal_code' => $store->postal_code,
                'address_line' => $store->address_line,
                'is_umkm' => $store->is_umkm,
                'response_time_label' => $store->response_time_label,
                'bank_name' => $store->bank_name,
                'bank_account_number' => $store->bank_account_number,
                'bank_account_name' => $store->bank_account_name,
                'logo_url' => $store->logo_url,
                'banner_url' => $store->banner_url,
            ],
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
            'typeOptions' => $this->typeOptions(),
            'taxStatusOptions' => $this->taxStatusOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument ?: $store->sellerDocument()->create(['submission_status' => 'draft']);

        $data = $request->validate([
            // Store Identity - Required
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($this->typeOptionValues())],
            'tax_status' => ['required', Rule::in($this->taxStatusOptionValues())],
            'phone' => ['required', 'string', 'max:20'],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],

            // Business Info - Required (except bumn_partner which is optional)
            'bumn_partner' => ['nullable', 'string', 'max:255'],
            'is_umkm' => ['nullable', 'boolean'],

            // Address - Required
            'province_id' => ['required', 'integer'],
            'city_id' => ['required', 'integer'],
            'district_id' => ['required', 'integer'],
            'postal_code' => ['required', 'string', 'max:10'],
            'address_line' => ['required', 'string', 'max:500'],
            'response_time_label' => ['required', 'string', 'max:100'],

            // Bank - Required
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:255'],

            // Files - Logo/Banner nullable (can be uploaded later)
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // Documents - Required docs nullable during draft, checked on submit
            'ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'npwp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'nib' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],

            // Optional Documents
            'company_statement' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $store->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'tax_status' => $data['tax_status'] ?? $store->tax_status,
            'phone' => $data['phone'] ?? $store->phone,
            'tagline' => $data['tagline'] ?? $store->tagline,
            'description' => $data['description'] ?? $store->description,
            'bumn_partner' => $data['bumn_partner'] ?? $store->bumn_partner,
            'province_id' => $data['province_id'] ?? $store->province_id,
            'city_id' => $data['city_id'] ?? $store->city_id,
            'district_id' => $data['district_id'] ?? $store->district_id,
            'postal_code' => $data['postal_code'] ?? $store->postal_code,
            'address_line' => $data['address_line'] ?? $store->address_line,
            'is_umkm' => $data['is_umkm'] ?? $store->is_umkm,
            'response_time_label' => $data['response_time_label'] ?? $store->response_time_label,
            'bank_name' => $data['bank_name'] ?? $store->bank_name,
            'bank_account_number' => $data['bank_account_number'] ?? $store->bank_account_number,
            'bank_account_name' => $data['bank_account_name'] ?? $store->bank_account_name,
        ]);

        // Also update user phone
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

        // Handle file uploads
        if ($request->hasFile('ktp')) {
            $sellerDocument->addMedia($request->file('ktp'))
                ->toMediaCollection('ktp');
            $sellerDocument->update(['ktp_status' => 'pending']);
        }

        if ($request->hasFile('npwp')) {
            $sellerDocument->addMedia($request->file('npwp'))
                ->toMediaCollection('npwp');
            $sellerDocument->update(['npwp_status' => 'pending']);
        }

        if ($request->hasFile('nib')) {
            $sellerDocument->addMedia($request->file('nib'))
                ->toMediaCollection('nib');
            $sellerDocument->update(['nib_status' => 'pending']);
        }

        if ($request->hasFile('company_statement')) {
            $sellerDocument->addMedia($request->file('company_statement'))
                ->toMediaCollection('company_statement');
        }

        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                $sellerDocument->addMedia($file)
                    ->toMediaCollection('supporting_documents');
            }
        }

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument ?: $store->sellerDocument()->create(['submission_status' => 'draft']);
        $sellerDocument->refresh();

        // Validate required fields and files
        if (! $store->name || ! $store->type) {
            return back()->with('error', 'Silakan lengkapi nama toko dan jenis toko.');
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

        return back()->with('success', 'Dokumen berhasil disubmit untuk diverifikasi. Tim kami akan meninjau dalam 1-3 hari kerja.');
    }

    public function deleteSupportingDocument(Request $request, int $mediaId): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument;

        $media = $sellerDocument->getMedia('supporting_documents')->firstWhere('id', $mediaId);

        if ($media) {
            $media->delete();
        }

        return back()->with('success', 'Dokumen berhasil dihapus.');
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

    private function typeOptionValues(): array
    {
        return array_map(static fn (array $option) => $option['value'], $this->typeOptions());
    }

    private function taxStatusOptions(): array
    {
        return [
            ['value' => 'pkp', 'label' => 'PKP'],
            ['value' => 'non_pkp', 'label' => 'Non PKP'],
        ];
    }

    private function taxStatusOptionValues(): array
    {
        return array_map(static fn (array $option) => $option['value'], $this->taxStatusOptions());
    }
}
