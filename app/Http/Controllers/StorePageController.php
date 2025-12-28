<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorePageController extends Controller
{
    public function __invoke(Request $request, Store $store): Response
    {
        $store->load([
            'provinceRegion:id,name',
            'cityRegion:id,name',
            'districtRegion:id,name',
        ]);
        $cityId = CustomerLocationResolver::resolveCityId($request);
        $cityId = CustomerLocationResolver::resolveCityId($request);

        $search = $request->string('search')->toString();
        $sort = $request->string('sort', 'latest')->toString();
        $status = $request->string('status')->toString();
        $priceMin = $request->integer('price_min');
        $priceMax = $request->integer('price_max');
        $certificates = collect($request->input('certificates', []))
            ->filter(fn ($value) => in_array($value, ['pdn', 'tkdn'], true))
            ->values()
            ->all();

        $productsQuery = $store->products()
            ->with(['store:id,is_umkm,tax_status', 'locationCity:id,name', 'locationProvince:id,name', 'media'])
            ->visibleForCity($cityId)
            ->when($search, fn ($query) => $query->search($search))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priceMin, fn ($query) => $query->where('price', '>=', $priceMin))
            ->when($priceMax, fn ($query) => $query->where('price', '<=', $priceMax))
            ->when(in_array('pdn', $certificates, true), fn ($query) => $query->where('is_pdn', true))
            ->when(in_array('tkdn', $certificates, true), fn ($query) => $query->where('is_tkdn', true));

        switch ($sort) {
            case 'price_desc':
                $productsQuery->orderByDesc('price');
                break;
            case 'price_asc':
                $productsQuery->orderBy('price');
                break;
            default:
                $productsQuery->orderByDesc('created_at');
        }

        $products = $productsQuery
            ->paginate(16)
            ->withQueryString()
            ->through(fn (Product $product) => $this->transformProduct($product, $store));

        return Inertia::render('Store', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'store' => $this->transformStore($store),
            'stats' => $this->storeStats($store),
            'filters' => [
                'search' => $search,
                'sort' => $sort ?: 'latest',
                'price_min' => $priceMin ?: null,
                'price_max' => $priceMax ?: null,
                'status' => $status ?: null,
                'certificates' => $certificates,
            ],
            'filterGroups' => $this->filterGroups(),
            'sortOptions' => $this->sortOptions(),
            'products' => $products,
            'reviews' => $this->getStoreReviews($store, $request),
        ]);
    }

    protected function transformStore(Store $store): array
    {
        return [
            'id' => $store->id,
            'slug' => $store->slug,
            'name' => $store->name,
            'tagline' => $store->tagline,
            'location' => $this->formatLocation($store),
            'is_umkm' => (bool) $store->is_umkm,
            'tax_status' => $this->formatTaxStatus($store->tax_status),
            'banner_url' => $store->banner_url,
            'logo_url' => $store->logo_url,
            'badges' => array_values(array_filter([
                $store->is_umkm ? 'UMKM' : 'Vendor',
                $store->tax_status ? $this->formatTaxStatus($store->tax_status) : null,
            ])),
        ];
    }

    protected function transformProduct(Product $product, Store $store): array
    {
        $price = $product->sale_price ?? $product->price ?? 0;
        $originalPrice = $product->sale_price ? $product->price : null;
        $location = $product->location_city ?? $store->city ?? $this->formatLocation($store);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'price' => $price,
            'originalPrice' => $originalPrice,
            'location' => $location,
            'tag' => $product->status === Product::STATUS_PRE_ORDER ? 'Pre Order' : 'Ready',
            'badges' => $this->productBadges($product, $store),
            'image' => $this->resolveProductImage($product),
        ];
    }

    protected function productBadges(Product $product, Store $store): array
    {
        return array_values(array_filter([
            $store->is_umkm ? 'UMKM' : null,
            $this->formatTaxStatus($store->tax_status),
            $product->is_pdn ? 'PDN' : null,
            $product->is_tkdn ? 'TKDN' : null,
        ]));
    }

    protected function storeStats(Store $store): array
    {
        return [
            [
                'label' => 'BUMN Pengampu',
                'value' => $store->bumn_partner ?: '-',
                'icon' => 'bumn',
            ],
            [
                'label' => 'Transaksi Selesai',
                'value' => number_format($store->transactions_count ?? 0),
                'icon' => 'transactions',
            ],
            [
                'label' => 'Rating & Ulasan',
                'value' => $store->rating ? number_format((float) $store->rating, 1) : '0',
                'icon' => 'rating',
            ],
            [
                'label' => 'Waktu Respons',
                'value' => $store->response_time_label ?: 'Cepat',
                'icon' => 'response',
            ],
        ];
    }

    protected function filterGroups(): array
    {
        return [
            [
                'title' => 'Kategori',
                'key' => null,
                'options' => [
                    'Jasa Percetakan & Media',
                    'Office & Stationery',
                    'Souvenir & Merchandise',
                    'Wedding',
                ],
            ],
            [
                'title' => 'Tipe Pembayaran',
                'key' => null,
                'options' => [
                    'Bisa Termin Jasa',
                    'Bisa Tempo 90 Hari',
                    'Bisa Tempo 120 Hari',
                    'Bisa Tempo 150 Hari',
                    'Bisa Tempo 180 Hari',
                ],
            ],
            [
                'title' => 'Stok Produk',
                'key' => 'status',
                'options' => [
                    ['label' => 'Ready', 'value' => Product::STATUS_READY],
                    ['label' => 'Pre Order', 'value' => Product::STATUS_PRE_ORDER],
                ],
            ],
            [
                'title' => 'Sertifikat',
                'key' => 'certificates',
                'options' => [
                    ['label' => 'Produk Dalam Negeri', 'value' => 'pdn'],
                    ['label' => 'TKDN', 'value' => 'tkdn'],
                ],
            ],
        ];
    }

    protected function sortOptions(): array
    {
        return [
            ['value' => 'latest', 'label' => 'Terbaru'],
            ['value' => 'price_desc', 'label' => 'Harga Tertinggi'],
            ['value' => 'price_asc', 'label' => 'Harga Terendah'],
        ];
    }

    protected function resolveProductImage(Product $product): string
    {
        $fromMedia = $product->getFirstMediaUrl('product_image');

        if ($fromMedia) {
            return $product->normalizeMediaUrl($fromMedia);
        }

        if ($product->image_url) {
            return $product->image_url;
        }

        return "https://picsum.photos/seed/{$product->slug}/800/400";
    }

    protected function formatLocation(Store $store): ?string
    {
        $city = $store->city;
        $province = $store->province;

        if ($city || $province) {
            return trim(($city ? "{$city}, " : '').($province ?? ''), ', ');
        }

        return null;
    }

    protected function formatTaxStatus(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return strtoupper($status) === 'PKP' ? 'PKP' : 'Non PKP';
    }

    protected function getStoreReviews(Store $store, Request $request): array
    {
        $productIds = $store->products()->pluck('id');

        // Get rating filter from request
        $ratingFilter = $request->integer('review_rating');

        // Calculate summary stats
        $allReviews = Review::whereIn('product_id', $productIds);
        $totalReviews = $allReviews->count();
        $averageRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0;

        // Get distribution
        $distribution = Review::whereIn('product_id', $productIds)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Fill missing ratings with 0
        $fullDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $fullDistribution[$i] = $distribution[$i] ?? 0;
        }

        // Get paginated reviews
        $reviewsQuery = Review::whereIn('product_id', $productIds)
            ->with(['user:id,name', 'product:id,name,slug'])
            ->when($ratingFilter, fn ($q) => $q->where('rating', $ratingFilter))
            ->orderByDesc('created_at');

        $reviews = $reviewsQuery
            ->paginate(10, ['*'], 'review_page')
            ->withQueryString()
            ->through(fn ($review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toDateTimeString(),
                'created_at_human' => $review->created_at?->diffForHumans(),
                'user' => [
                    'name' => $review->user?->name ?? 'Pembeli',
                    'initial' => strtoupper(substr($review->user?->name ?? 'P', 0, 1)),
                ],
                'product' => [
                    'id' => $review->product?->id,
                    'name' => $review->product?->name,
                    'slug' => $review->product?->slug,
                ],
            ]);

        return [
            'summary' => [
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'distribution' => $fullDistribution,
            ],
            'items' => $reviews,
            'filter' => [
                'rating' => $ratingFilter ?: null,
            ],
        ];
    }
}
