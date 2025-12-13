<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\Province;
use Inertia\Inertia;
use Inertia\Response;

class SearchPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $cityId = CustomerLocationResolver::resolveCityId($request);
        $query = $request->string('q', $request->string('search'))->toString();
        $status = $request->string('status')->toString();
        $sellerType = $request->string('seller_type')->toString();
        $itemType = $request->string('item_type')->toString();
        $priceMin = $request->integer('price_min');
        $priceMax = $request->integer('price_max');
        $sort = $request->string('sort', 'latest')->toString();
        $location = $request->string('location')->toString();
        $rating = $request->integer('rating');

        $badges = collect($request->input('badges', []))
            ->filter(fn ($badge) => in_array($badge, ['pdn', 'pkp', 'tkdn'], true))
            ->values()
            ->all();

        $productsQuery = Product::with([
            'store:id,name,is_umkm,rating,province_id,city_id,district_id,postal_code',
            'locationCity:id,name',
            'locationProvince:id,name',
            'media',
        ])
            ->visibleForCity($cityId)
            ->search($query)
            ->when($location, function ($query) use ($location) {
                $provinceId = Province::where('name', $location)->value('id');
                if ($provinceId) {
                    $query->where('location_province_id', $provinceId);
                }
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($itemType, fn ($query) => $query->where('item_type', $itemType))
            ->when($sellerType === 'umkm', fn ($query) => $query->whereHas('store', fn ($q) => $q->where('is_umkm', true)))
            ->when($sellerType === 'non_umkm', fn ($query) => $query->whereHas('store', fn ($q) => $q->where('is_umkm', false)))
            ->when($priceMin, fn ($query) => $query->where('price', '>=', $priceMin))
            ->when($priceMax, fn ($query) => $query->where('price', '<=', $priceMax))
            ->when($rating, fn ($query) => $query->whereHas('store', fn ($q) => $q->where('rating', '>=', $rating)))
            ->when(in_array('pdn', $badges, true), fn ($query) => $query->where('is_pdn', true))
            ->when(in_array('pkp', $badges, true), fn ($query) => $query->where('is_pkp', true))
            ->when(in_array('tkdn', $badges, true), fn ($query) => $query->where('is_tkdn', true));

        switch ($sort) {
            case 'price_asc':
                $productsQuery->orderBy('price');
                break;
            case 'price_desc':
                $productsQuery->orderByDesc('price');
                break;
            case 'popular':
                $productsQuery->orderByDesc('stock');
                break;
            default:
                $productsQuery->orderByDesc('created_at');
        }

        $products = $productsQuery
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'min_order' => $product->min_order,
                'location_city' => $product->location_city ?? $product->store?->city,
                'location_province' => $product->location_province ?? $product->store?->province,
                'status' => $product->status,
                'is_pdn' => $product->is_pdn,
                'is_pkp' => $product->is_pkp,
                'is_tkdn' => $product->is_tkdn,
                'item_type' => $product->item_type,
                'store' => $product->store ? [
                    'id' => $product->store->id,
                    'name' => $product->store->name,
                    'is_umkm' => $product->store->is_umkm,
                    'rating' => $product->store->rating,
                ] : null,
                'image_url' => $this->resolveProductImage($product),
                'url' => route('product.detail', ['slug' => $product->slug, 'product' => $product]),
            ]);

        $locations = Product::query()
            ->visibleForCity($cityId)
            ->when($query, fn ($q) => $q->search($query))
            ->whereNotNull('location_province_id')
            ->distinct()
            ->with('locationProvince')
            ->get()
            ->pluck('locationProvince.name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Category', [
            'category' => [
                'id' => null,
                'name' => 'Semua Produk',
                'slug' => 'search',
            ],
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'products' => $products,
            'filters' => [
                'search' => $query,
                'location' => $location ?: null,
                'status' => $status ?: null,
                'seller_type' => $sellerType ?: null,
                'price_min' => $priceMin ?: null,
                'price_max' => $priceMax ?: null,
                'badges' => $badges,
                'sort' => $sort ?: 'latest',
                'item_type' => $itemType ?: null,
                'rating' => $rating ?: null,
                'child_category' => null,
            ],
            'options' => [
                'locations' => $locations,
                'statuses' => Product::statuses(),
                'itemTypes' => Product::itemTypes(),
                'sellerTypes' => [
                    ['value' => 'umkm', 'label' => 'UMKM'],
                    ['value' => 'non_umkm', 'label' => 'Non UMKM'],
                ],
                'badgeOptions' => [
                    ['value' => 'pdn', 'label' => 'PDN'],
                    ['value' => 'pkp', 'label' => 'PKP'],
                    ['value' => 'tkdn', 'label' => 'TKDN'],
                ],
                'priceRanges' => [
                    ['label' => '< Rp500 ribu', 'min' => null, 'max' => 500_000],
                    ['label' => 'Rp500 rb - Rp1 jt', 'min' => 500_000, 'max' => 1_000_000],
                    ['label' => 'Rp1 jt - Rp5 jt', 'min' => 1_000_000, 'max' => 5_000_000],
                    ['label' => '> Rp5 jt', 'min' => 5_000_000, 'max' => null],
                ],
                'ratingOptions' => [
                    ['label' => '4+ Bintang', 'value' => 4],
                    ['label' => '3+ Bintang', 'value' => 3],
                ],
                'sortOptions' => [
                    ['value' => 'latest', 'label' => 'Terbaru'],
                    ['value' => 'price_asc', 'label' => 'Harga Terendah'],
                    ['value' => 'price_desc', 'label' => 'Harga Tertinggi'],
                    ['value' => 'popular', 'label' => 'Populer'],
                ],
                'childCategories' => collect(),
            ],
        ]);
    }

    private function resolveProductImage(Product $product): ?string
    {
        $fromMedia = $product->getFirstMediaUrl('product_image');
        if ($fromMedia) {
            return $product->normalizeMediaUrl($fromMedia);
        }

        return $product->image_url;
    }
}
