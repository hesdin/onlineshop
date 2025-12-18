<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'store' => $store,
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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->with('sellerDocument')->firstOrFail();
        $sellerDocument = $store->sellerDocument ?: $store->sellerDocument()->create(['submission_status' => 'draft']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($this->typeOptionValues())],
            'address_line' => ['nullable', 'string', 'max:500'],

            // Files
            'ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'npwp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'nib' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'company_statement' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $store->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'address_line' => $data['address_line'] ?? $store->address_line,
        ]);

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
}
