<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollectionPageController extends Controller
{
    public function __invoke(Request $request, Collection $collection): Response
    {
        $cityId = CustomerLocationResolver::resolveCityId($request);
        $isAuthenticated = $request->user() !== null;

        $products = $collection->products()
            ->with([
                'store:id,name,is_umkm,tax_status,rating,province_id,city_id,district_id',
                'store.cityRegion:id,name',
                'store.provinceRegion:id,name',
                'locationCity:id,name',
                'locationProvince:id,name',
                'media',
            ])
            ->visibleForCityAuth($cityId, $isAuthenticated)
            ->orderByDesc('products.created_at')
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Product $product) => $this->transformProduct($product));

        return Inertia::render('Collection/Show', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'collection' => [
                'id' => $collection->id,
                'title' => $collection->title,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'banner' => $this->formatMediaUrl($collection->getFirstMediaUrl('cover_image')),
            ],
            'products' => $products,
        ]);
    }

    private function formatMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    private function transformProduct(Product $product): array
    {
        $price = $product->sale_price ?? $product->price ?? 0;
        $badge = $product->store?->is_umkm ? 'UMKM' : 'Vendor';
        $location = $product->location_city
            ?? $product->store?->city
            ?? $product->location_province
            ?? $product->store?->province
            ?? 'Lokasi tidak tersedia';

        $status = null;
        if ($product->status === Product::STATUS_PRE_ORDER) {
            $status = 'Pre Order';
        } elseif ($product->sale_price && $product->price && $product->price > $product->sale_price) {
            $status = 'Diskon '.(int) round((($product->price - $product->sale_price) / $product->price) * 100).'%';
        }

        $tags = collect([
            $product->is_pdn ? 'PDN' : null,
            $product->store?->tax_status ?: null,
        ])
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->name,
            'vendor' => $product->store?->name ?? 'Toko',
            'price' => $price,
            'badge' => $badge,
            'location' => $location,
            'sold' => $product->stock ? "Terjual {$product->stock}" : null,
            'status' => $status,
            'tags' => $tags,
            'image' => $this->resolveProductImage($product),
        ];
    }

    private function resolveProductImage(Product $product): ?string
    {
        $fromMedia = $product->getFirstMediaUrl('product_image');

        if ($fromMedia) {
            return $this->formatMediaUrl($fromMedia);
        }

        if ($product->image_url) {
            return $product->image_url;
        }

        return null;
    }
}

