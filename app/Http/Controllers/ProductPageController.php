<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Product;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPageController extends Controller
{
    public function __invoke(Request $request, string $slug, Product $product): Response|RedirectResponse
    {
        if ($product->slug !== $slug) {
            return redirect()->route('product.detail', ['slug' => $product->slug, 'product' => $product->getKey()]);
        }

        $cityId = CustomerLocationResolver::resolveCityId($request);

        if (! $product->isVisibleForCity($cityId)) {
            abort(404);
        }

        $product->load([
            'store:id,name,slug,is_umkm,tax_status,rating,transactions_count,bumn_partner,response_time_label,province_id,city_id,district_id',
            'store.provinceRegion:id,name',
            'store.cityRegion:id,name',
            'store.districtRegion:id,name',
            'category:id,name,slug',
        ]);

        return Inertia::render('ProductDetail', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'product' => $this->transformProduct($product, $cityId),
            'customerAddress' => $this->customerAddress($request),
        ]);
    }

    protected function transformProduct(Product $product, ?int $customerCityId = null): array
    {
        $price = $product->sale_price ?? $product->price ?? 0;
        $originalPrice = $product->sale_price ? $product->price : null;
        $location = $this->formatLocation($product);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'code' => (string) $product->getKey(),
            'name' => $product->name,
            'category' => $product->category?->name,
            'categoryUrl' => $product->category ? route('category.show', $product->category) : null,
            'price' => $price,
            'originalPrice' => $originalPrice,
            'sold' => $product->stock ?? 0,
            'rating' => (float) ($product->store?->rating ?? 0),
            'reviewCount' => 0,
            'stock' => $product->stock ?? 0,
            'badges' => $this->buildBadges($product),
            'minOrder' => $product->min_order ? "{$product->min_order} pcs" : null,
            'minOrderQuantity' => $product->min_order ?: 1,
            'brand' => $product->brand,
            'weight' => $this->formatWeight($product->weight),
            'dimensions' => $this->formatDimensions($product),
            'volumeWeight' => $this->calculateVolumeWeight($product),
            'categories' => $product->category?->name,
            'location' => $location,
            'description' => $product->description,
            'gallery' => $this->buildGallery($product),
            'info' => $this->buildInfo($product),
            'store' => $this->buildStore($product, $location),
            'otherProducts' => $this->buildOtherProducts($product, $customerCityId),
        ];
    }

    protected function buildBadges(Product $product): array
    {
        return collect([
            $product->is_pdn ? 'PDN' : null,
            $product->is_pkp ? 'PKP' : null,
            $product->is_tkdn ? 'TKDN' : null,
        ])
            ->filter()
            ->values()
            ->all();
    }

    protected function buildGallery(Product $product): array
    {
        $mediaGallery = $product->getMedia('product_image')
            ->map(fn ($media) => $product->normalizeMediaUrl($media->getUrl()));

        return $mediaGallery
            ->whenEmpty(fn () => collect([$product->image_url]))
            ->filter()
            ->values()
            ->all();
    }

    protected function buildInfo(Product $product): array
    {
        $info = [];

        if ($product->category?->name) {
            $info[] = ['label' => 'Kategori', 'value' => $product->category->name];
        }

        if ($product->brand) {
            $info[] = ['label' => 'Brand', 'value' => $product->brand];
        }

        if ($product->min_order) {
            $info[] = ['label' => 'Min Pembelian', 'value' => "{$product->min_order} pcs"];
        }

        if ($product->weight) {
            $info[] = ['label' => 'Berat Satuan', 'value' => $this->formatWeight($product->weight)];
        }

        $dimensions = $this->formatDimensions($product);
        $volumeWeight = $this->calculateVolumeWeight($product);

        if ($dimensions) {
            $info[] = [
                'label' => 'Dimensi Ukuran',
                'value' => $dimensions,
                'helper' => $volumeWeight ? "(Berat volume: {$volumeWeight})" : null,
            ];
        }

        return $info;
    }

    protected function buildStore(Product $product, ?string $location): array
    {
        $store = $product->store;
        $storeLocation = $store?->city || $store?->province
            ? trim(($store?->city ? "{$store->city}, " : '').($store?->province ?? ''), ', ')
            : $location;

        return [
            'name' => $store?->name,
            'id' => $store?->id,
            'slug' => $store?->slug,
            'isUmkm' => (bool) ($store?->is_umkm ?? false),
            'taxStatus' => $store?->tax_status ?? 'Non PKP',
            'location' => $storeLocation,
            'avatar' => "https://picsum.photos/seed/store-{$store?->id}/200/200",
            'highlights' => $this->storeHighlights($store),
            'transactionsCount' => (int) ($store?->transactions_count ?? 0),
            'rating' => (float) ($store?->rating ?? 0),
            'url' => $store?->slug ? route('store.show', ['store' => $store->slug]) : null,
        ];
    }

    protected function buildOtherProducts(Product $product, ?int $customerCityId): array
    {
        if (! $product->store_id) {
            return [];
        }

        return Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sale_price', 'location_city_id', 'location_province_id', 'location_district_id', 'location_postal_code', 'is_pdn', 'is_pkp', 'is_tkdn', 'store_id'])
            ->where('store_id', $product->store_id)
            ->whereKeyNot($product->getKey())
            ->visibleForCity($customerCityId)
            ->latest()
            ->limit(7)
            ->with(['store:id,is_umkm', 'media'])
            ->get()
            ->map(function (Product $item) {
                $price = $item->sale_price ?? $item->price ?? 0;
                $originalPrice = $item->sale_price ? $item->price : null;
                $discountPercent = ($originalPrice && $originalPrice > $price)
                    ? (int) round((($originalPrice - $price) / $originalPrice) * 100)
                    : null;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $price,
                    'originalPrice' => $originalPrice,
                    'discountPercent' => $discountPercent,
                    'location' => $this->formatLocation($item),
                    'badge' => $item->store?->is_umkm ? 'UMKM' : null,
                    'tags' => collect([
                        $item->is_pkp ? 'PKP' : null,
                        $item->is_pdn ? 'PDN' : null,
                        $item->is_tkdn ? 'TKDN' : null,
                    ])
                        ->filter()
                        ->values()
                        ->all(),
                    'image' => $item->image_url,
                ];
            })
            ->values()
            ->all();
    }

    protected function customerAddress(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $address = Address::query()
            ->where('user_id', $user->id)
            ->with(['provinceRegion:id,name', 'cityRegion:id,name', 'districtRegion:id,name'])
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->first();

        if (! $address) {
            return null;
        }

        $fullAddress = implode(', ', array_filter([
            $address->address_line,
            $address->district,
            $address->city,
            $address->province,
            $address->postal_code,
        ]));

        return [
            'id' => $address->id,
            'label' => $address->label ?: 'Alamat Utama',
            'recipient' => $address->recipient_name ?: $user->name,
            'phone' => $address->phone,
            'full_address' => $fullAddress,
            'address' => $address->address_line,
            'district' => $address->district,
            'city' => $address->city,
            'province' => $address->province,
        ];
    }

    protected function storeHighlights($store): array
    {
        if (! $store) {
            return [];
        }

        return collect([
            $store->bumn_partner ? 'BUMN Pengampu' : null,
            $store->transactions_count ? "{$store->transactions_count} Transaksi Selesai" : null,
            $store->rating ? 'Rating & Ulasan' : null,
            $store->response_time_label ? "Respon {$store->response_time_label}" : null,
        ])
            ->filter()
            ->values()
            ->all();
    }

    protected function formatLocation(Product $product): ?string
    {
        $city = $product->location_city;
        $province = $product->location_province;

        if ($city || $province) {
            return trim(($city ? "{$city}, " : '').($province ?? ''), ', ');
        }

        return null;
    }

    protected function formatWeight(?int $weight): ?string
    {
        if (! $weight) {
            return null;
        }

        if ($weight >= 1000) {
            return number_format($weight / 1000, 1).' kg';
        }

        return "{$weight} gram";
    }

    protected function formatDimensions(Product $product): ?string
    {
        if (! $product->length || ! $product->width || ! $product->height) {
            return null;
        }

        $length = rtrim(rtrim(number_format($product->length, 2, '.', ''), '0'), '.');
        $width = rtrim(rtrim(number_format($product->width, 2, '.', ''), '0'), '.');
        $height = rtrim(rtrim(number_format($product->height, 2, '.', ''), '0'), '.');

        return "{$length}x{$width}x{$height} cm";
    }

    protected function calculateVolumeWeight(Product $product): ?string
    {
        if (! $product->length || ! $product->width || ! $product->height) {
            return null;
        }

        $volumeWeight = ($product->length * $product->width * $product->height) / 6000;

        return number_format($volumeWeight, 1).' kg';
    }
}
