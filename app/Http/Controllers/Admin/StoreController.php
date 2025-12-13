<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Store;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $taxStatus = $request->string('tax_status')->toString();

        $stores = Store::query()
            ->withCount('products')
            ->when($search, fn (Builder $query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('cityRegion', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('provinceRegion', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('districtRegion', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            }))
            ->when($type, fn (Builder $query) => $query->where('type', $type))
            ->when($taxStatus, fn (Builder $query) => $query->where('tax_status', $taxStatus))
            ->when($status === 'verified', fn (Builder $query) => $query->where('is_verified', true))
            ->when($status === 'unverified', fn (Builder $query) => $query->where('is_verified', false))
            ->orderByDesc('is_verified')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Store $store) => [
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
                'address_line' => $store->address_line,
                'is_verified' => $store->is_verified,
                'is_umkm' => $store->is_umkm,
                'response_time_label' => $store->response_time_label,
                'rating' => $store->rating,
                'transactions_count' => $store->transactions_count,
                'products_count' => $store->products_count,
            ]);

        return Inertia::render('Admin/Stores/Index', [
            'stores' => $stores,
            'filters' => [
                'search' => $search,
                'type' => $type ?: null,
                'status' => $status ?: null,
                'tax_status' => $taxStatus ?: null,
            ],
            ...$this->formOptions(),
            'metrics' => [
                'total' => Store::count(),
                'verified' => Store::where('is_verified', true)->count(),
                'umkm' => Store::where('is_umkm', true)->count(),
            ],
        ]);
    }

    public function create(RegionService $regions): Response
    {
        return Inertia::render('Admin/Stores/Create', [
            ...$this->formOptions(),
            'store' => $this->defaultStorePayload(),
            'regionOptions' => $this->regionOptions($regions),
            'sellerOptions' => $this->sellerOptions(),
        ]);
    }

    public function edit(Store $store, RegionService $regions): Response
    {
        return Inertia::render('Admin/Stores/Edit', [
            ...$this->formOptions(),
            'store' => $this->formatStore($store),
            'regionOptions' => $this->regionOptions($regions, $store),
            'sellerOptions' => $this->sellerOptions($store->user_id),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request->validated());
        $store = Store::create($data);

        $this->syncDocuments($store, $request);

        return Redirect::route('admin.stores.index')->with('success', 'Toko berhasil dibuat.');
    }

    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        $store->update($this->prepareData($request->validated()));

        $this->syncDocuments($store, $request);

        return Redirect::route('admin.stores.index')->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();

        return Redirect::route('admin.stores.index')->with('success', 'Toko berhasil dihapus.');
    }

    private function prepareData(array $data): array
    {
        unset(
            $data['owner_id_card'],
            $data['nib_document'],
            $data['npwp_document'],
            $data['business_license'],
            $data['pkp_document'],
        );

        $slug = $data['slug'] ?? null;
        $data['slug'] = Str::slug($slug ?: $data['name']);

        $data['rating'] = array_key_exists('rating', $data) && $data['rating'] !== null && $data['rating'] !== ''
            ? (float) $data['rating']
            : null;

        $data['transactions_count'] = array_key_exists('transactions_count', $data) && $data['transactions_count'] !== null && $data['transactions_count'] !== ''
            ? (int) $data['transactions_count']
            : 0;

        return $data;
    }

    private function syncDocuments(Store $store, Request $request): void
    {
        $fileCollections = [
            'owner_id_card' => 'owner_id_card',
            'nib_document' => 'nib_document',
            'npwp_document' => 'npwp_document',
            'business_license' => 'business_license',
            'pkp_document' => 'pkp_document',
        ];

        foreach ($fileCollections as $field => $collection) {
            if ($request->hasFile($field) && $request->file($field) instanceof UploadedFile) {
                $store->clearMediaCollection($collection);
                $store->addMediaFromRequest($field)->toMediaCollection($collection);
            }
        }
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
            'user_id' => $store->user_id,
            'province_id' => $store->province_id,
            'city_id' => $store->city_id,
            'district_id' => $store->district_id,
            'postal_code' => $store->postal_code,
            'address_line' => $store->address_line,
            'is_verified' => $store->is_verified,
            'is_umkm' => $store->is_umkm,
            'response_time_label' => $store->response_time_label,
            'rating' => $store->rating,
            'transactions_count' => $store->transactions_count,
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
            'rating' => null,
            'transactions_count' => 0,
            'user_id' => null,
        ];
    }

    private function formOptions(): array
    {
        return [
            'typeOptions' => $this->typeOptions(),
            'taxStatusOptions' => $this->taxStatusOptions(),
        ];
    }

    private function regionOptions(RegionService $regions, ?Store $store = null): array
    {
        $provinces = $regions->provinces();
        $cities = $regions->cities();
        $districts = $regions->districts();

        return [
            'provinces' => $provinces->map(fn ($p) => ['id' => $p->id, 'code' => $p->code, 'name' => $p->name])->values(),
            'cities' => $cities->map(fn ($c) => ['id' => $c->id, 'code' => $c->code, 'name' => $c->name, 'province_code' => $c->province_code])->values(),
            'districts' => $districts->map(fn ($d) => ['id' => $d->id, 'code' => $d->code, 'name' => $d->name, 'city_code' => $d->city_code])->values(),
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

    private function sellerOptions(?int $currentUserId = null): array
    {
        $sellers = User::role('seller')
            ->select(['id', 'name', 'email', 'email_verified_at'])
            ->whereNotNull('email_verified_at')
            ->orderBy('name')
            ->limit(200)
            ->get();

        if ($currentUserId && ! $sellers->firstWhere('id', $currentUserId)) {
            $currentUser = User::find($currentUserId);
            if ($currentUser) {
                $sellers->push($currentUser);
            }
        }

        return $sellers
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => (bool) $user->email_verified_at,
            ])
            ->values()
            ->all();
    }
}
