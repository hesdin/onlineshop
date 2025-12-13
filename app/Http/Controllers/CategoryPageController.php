<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\CustomerLocationResolver;
use Laravolt\Indonesia\Models\Province;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryPageController extends Controller
{
    public function __invoke(Request $request, Category $category): Response
    {
        $cityId = CustomerLocationResolver::resolveCityId($request);
        $search = $request->string('search')->toString();
        $location = $request->string('location')->toString();
        $status = $request->string('status')->toString();
        $sellerType = $request->string('seller_type')->toString();
        $priceMin = $request->integer('price_min');
        $priceMax = $request->integer('price_max');
        $itemType = $request->string('item_type')->toString();
        $rating = $request->integer('rating');
        $sort = $request->string('sort', 'latest')->toString();
        $childCategoryId = $request->integer('child_category');

        $badges = collect($request->input('badges', []))
            ->filter(fn ($badge) => in_array($badge, ['pdn', 'pkp', 'tkdn'], true))
            ->values()
            ->all();

        $categoryIds = $childCategoryId
            ? [$childCategoryId]
            : $category->children()->pluck('id')->push($category->id)->all();

        $productsQuery = Product::with([
            'store:id,name,is_umkm,rating,province_id,city_id,district_id,postal_code',
            'locationCity:id,name',
            'locationProvince:id,name',
            'media',
        ])
            ->visibleForCity($cityId)
            ->whereIn('category_id', $categoryIds)
            ->when($search, fn ($query) => $query->search($search))
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
            ->when($rating, fn ($query) => $query->whereHas('store', fn ($q) => $q->where('rating', '>=', $rating)))
            ->when($priceMin, fn ($query) => $query->where('price', '>=', $priceMin))
            ->when($priceMax, fn ($query) => $query->where('price', '<=', $priceMax))
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
            ]);

        $locations = Product::query()
            ->visibleForCity($cityId)
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('location_province_id')
            ->distinct()
            ->with('locationProvince')
            ->get()
            ->pluck('locationProvince.name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $childCategories = $category->children()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Category', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'products' => $products,
            'filters' => [
                'search' => $search,
                'location' => $location ?: null,
                'status' => $status ?: null,
                'seller_type' => $sellerType ?: null,
                'price_min' => $priceMin ?: null,
                'price_max' => $priceMax ?: null,
                'badges' => $badges,
                'sort' => $sort,
                'item_type' => $itemType ?: null,
                'rating' => $rating ?: null,
                'child_category' => $childCategoryId ?: null,
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
                'childCategories' => $childCategories,
            ],
        ]);
    }

    private function resolveProductImage(Product $product): ?string
    {
        // Prioritaskan media yang diunggah sebagai gambar utama
        $fromMedia = $product->getFirstMediaUrl('product_image');
        if ($fromMedia) {
            return $product->normalizeMediaUrl($fromMedia);
        }

        // Fallback ke atribut bawaan (jika ada)
        return $product->image_url;
    }
}
