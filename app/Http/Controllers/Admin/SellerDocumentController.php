<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationReceived;
use App\Http\Controllers\Controller;
use App\Models\SellerDocument;
use App\Notifications\StoreVerifiedNotification;
use App\Notifications\StoreRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SellerDocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $query = SellerDocument::query()
            ->with(['store.user', 'media'])
            ->when($status, fn ($q) => $q->where('submission_status', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('store', function ($storeQuery) use ($search) {
                    $storeQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw("CASE submission_status WHEN 'submitted' THEN 1 WHEN 'rejected' THEN 2 WHEN 'approved' THEN 3 WHEN 'draft' THEN 4 ELSE 5 END")
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        $documents = $query
            ->paginate(10)
            ->withQueryString()
            ->through(function (SellerDocument $document) {
                $store = $document->store;
                $user = $store?->user;

                $hasKtp = (bool) $document->getFirstMedia('ktp');
                $hasNpwp = (bool) $document->getFirstMedia('npwp');
                $hasNib = (bool) $document->getFirstMedia('nib');

                return [
                    'id' => $document->id,
                    'store_id' => $store?->id,
                    'store_name' => $store?->name,
                    'store_type' => $store?->type,
                    'store_is_verified' => (bool) ($store?->is_verified ?? false),
                    'owner_name' => $user?->name,
                    'owner_email' => $user?->email,
                    'submission_status' => $document->submission_status,
                    'submitted_at' => optional($document->submitted_at)->toDateTimeString(),
                    'reviewed_at' => optional($document->reviewed_at)->toDateTimeString(),
                    'ktp_status' => $document->ktp_status,
                    'npwp_status' => $document->npwp_status,
                    'nib_status' => $document->nib_status,
                    'has_ktp' => $hasKtp,
                    'has_npwp' => $hasNpwp,
                    'has_nib' => $hasNib,
                    'missing_required' => ! ($hasKtp && $hasNpwp && $hasNib),
                ];
            });

        $metrics = SellerDocument::query()
            ->select('submission_status', DB::raw('count(*) as aggregate'))
            ->groupBy('submission_status')
            ->pluck('aggregate', 'submission_status')
            ->all();

        return Inertia::render('Admin/SellerDocuments/Index', [
            'documents' => $documents,
            'filters' => [
                'search' => $search ?: null,
                'status' => $status ?: null,
            ],
            'metrics' => [
                'draft' => (int) ($metrics['draft'] ?? 0),
                'submitted' => (int) ($metrics['submitted'] ?? 0),
                'approved' => (int) ($metrics['approved'] ?? 0),
                'rejected' => (int) ($metrics['rejected'] ?? 0),
            ],
        ]);
    }

    public function show(SellerDocument $sellerDocument): Response
    {
        $sellerDocument->loadMissing(['store.user', 'media', 'reviewer']);

        $store = $sellerDocument->store;
        $user = $store?->user;

        return Inertia::render('Admin/SellerDocuments/Show', [
            'document' => [
                'id' => $sellerDocument->id,
                'submission_status' => $sellerDocument->submission_status,
                'admin_notes' => $sellerDocument->admin_notes,
                'submitted_at' => optional($sellerDocument->submitted_at)->toDateTimeString(),
                'reviewed_at' => optional($sellerDocument->reviewed_at)->toDateTimeString(),
                'reviewed_by' => $sellerDocument->reviewed_by,
                'reviewer_name' => $sellerDocument->reviewer?->name,
                'ktp_status' => $sellerDocument->ktp_status,
                'npwp_status' => $sellerDocument->npwp_status,
                'nib_status' => $sellerDocument->nib_status,
                'ktp_url' => $sellerDocument->ktp_url,
                'npwp_url' => $sellerDocument->npwp_url,
                'nib_url' => $sellerDocument->nib_url,
                'company_statement_url' => $sellerDocument->company_statement_url,
                'supporting_documents_urls' => $sellerDocument->supporting_documents_urls,
            ],
            'store' => [
                'id' => $store?->id,
                'name' => $store?->name,
                'type' => $store?->type,
                'is_verified' => (bool) ($store?->is_verified ?? false),
            ],
            'owner' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
        ]);
    }

    public function update(Request $request, SellerDocument $sellerDocument): RedirectResponse
    {
        $sellerDocument->loadMissing(['store.user', 'media']);
        $store = $sellerDocument->store;
        $previousStatus = $sellerDocument->submission_status;

        $data = $request->validate([
            'submission_status' => ['required', Rule::in(['submitted', 'approved', 'rejected', 'draft'])],
            'ktp_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'npwp_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'nib_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $hasKtp = (bool) $sellerDocument->getFirstMedia('ktp');
        $hasNpwp = (bool) $sellerDocument->getFirstMedia('npwp');
        $hasNib = (bool) $sellerDocument->getFirstMedia('nib');

        if ($data['submission_status'] === 'approved' && ! ($hasKtp && $hasNpwp && $hasNib)) {
            return back()->with('error', 'Dokumen wajib belum lengkap. Pastikan KTP, NPWP, dan NIB sudah diunggah.');
        }

        // Auto-determine submission_status based on individual document statuses
        $allApproved = $data['ktp_status'] === 'approved'
            && $data['npwp_status'] === 'approved'
            && $data['nib_status'] === 'approved';

        $anyRejected = in_array('rejected', [$data['ktp_status'], $data['npwp_status'], $data['nib_status']], true);

        // Logic priority:
        // 1. If any document is rejected -> submission_status = rejected
        // 2. If all documents are approved -> submission_status = approved
        // 3. If mixed (some approved, some pending, none rejected) -> submission_status = submitted
        if ($anyRejected) {
            $data['submission_status'] = 'rejected';
        } elseif ($allApproved && $hasKtp && $hasNpwp && $hasNib) {
            $data['submission_status'] = 'approved';
        } elseif ($data['submission_status'] === 'approved') {
            // Admin tries to approve but not all documents are approved yet
            return back()->with('error', 'Tidak dapat approve. Pastikan semua dokumen (KTP, NPWP, NIB) sudah disetujui terlebih dahulu.');
        }

        $sellerDocument->update([
            'submission_status' => $data['submission_status'],
            'ktp_status' => $data['ktp_status'],
            'npwp_status' => $data['npwp_status'],
            'nib_status' => $data['nib_status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        if ($store && $data['submission_status'] === 'approved') {
            $store->update(['is_verified' => true]);

            // Notify seller about store verification (database + email)
            if ($store->user) {
                Notification::sendNow($store->user, new StoreVerifiedNotification($store->name));
                $this->dispatchRealtimeNotification($store->user);
            }
        }

        if ($store && $data['submission_status'] === 'rejected' && $previousStatus !== 'rejected') {
            $store->update(['is_verified' => false]);

            // Notify seller about rejection (database + email)
            if ($store->user) {
                Notification::sendNow($store->user, new StoreRejectedNotification($store->name, $data['admin_notes']));
                $this->dispatchRealtimeNotification($store->user);
            }
        }

        // Provide clear feedback
        $message = 'Review dokumen berhasil disimpan.';
        if ($data['submission_status'] === 'approved') {
            $message = 'Dokumen berhasil disetujui. Toko sekarang terverifikasi.';
        } elseif ($anyRejected) {
            $rejectedDocs = [];
            if ($data['ktp_status'] === 'rejected') $rejectedDocs[] = 'KTP';
            if ($data['npwp_status'] === 'rejected') $rejectedDocs[] = 'NPWP';
            if ($data['nib_status'] === 'rejected') $rejectedDocs[] = 'NIB';
            $message = 'Dokumen ditolak: ' . implode(', ', $rejectedDocs) . '. Seller akan mendapat notifikasi untuk upload ulang.';
        }

        return back()->with('success', $message);
    }

    /**
     * Dispatch realtime notification event for the given user.
     */
    private function dispatchRealtimeNotification($user): void
    {
        $notification = $user->notifications()->latest()->first();
        if ($notification) {
            event(new NotificationReceived($user, [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'title' => $notification->data['title'] ?? 'Notifikasi',
                'message' => $notification->data['message'] ?? '',
                'icon' => $notification->data['icon'] ?? 'bell',
                'action_url' => $notification->data['action_url'] ?? null,
                'read_at' => null,
                'created_at' => $notification->created_at->diffForHumans(),
            ]));
        }
    }
}
