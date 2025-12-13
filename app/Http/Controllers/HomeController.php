<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $cityId = CustomerLocationResolver::resolveCityId($request);

        $categories = Category::orderBy('name')
            ->take(17)
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $this->formatMediaUrl(
                    $category->getFirstMediaUrl('category_image', 'thumb')
                        ?: $category->getFirstMediaUrl('category_image')
                ),
            ])
            ->values();

        $collections = Collection::query()
            ->with(['products' => function ($query) use ($cityId) {
                $query->with([
                    'store:id,name,is_umkm,province_id,city_id,district_id',
                    'store.cityRegion:id,name',
                    'store.provinceRegion:id,name',
                    'media',
                ])
                    ->visibleForCity($cityId)
                    ->orderBy('products.created_at', 'desc')
                    ->take(12);
            }])
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->map(fn (Collection $collection) => $this->transformCollection($collection))
            ->values();

        return Inertia::render('Home', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'categories' => $categories,
            'collections' => $collections,
        ]);
    }

    private function formatMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    private function transformCollection(Collection $collection): array
    {
        return [
            'id' => $collection->id,
            'title' => $collection->title,
            'slug' => $collection->slug,
            'color' => $collection->color_theme ?: 'from-sky-600 to-sky-500',
            'banner' => $this->formatMediaUrl($collection->getFirstMediaUrl('cover_image')) ?: 'https://smb-padiumkm-images-public-prod.oss-ap-southeast-5.aliyuncs.com/product-collection/image_section_banner/24112025/superdeal-road-to-padi-business-forum-and-showcase/ed27e7533e0ae63bb045d2520334d1.jpg',
            'url' => "/template/collection/{$collection->slug}",
            'products' => $collection->products
                ->map(fn ($product) => $this->transformProduct($product))
                ->values(),
        ];
    }

    private function transformProduct($product): array
    {
        $price = $product->sale_price ?? $product->price ?? 0;
        $badge = $product->store?->is_umkm ? 'UMKM' : 'Vendor';
        $location = $product->location_city
            ?? $product->store?->city
            ?? $product->location_province
            ?? $product->store?->province
            ?? 'Lokasi tidak tersedia';

        return [
            'id' => $product->id,
            'title' => $product->name,
            'slug' => $product->slug,
            'vendor' => $product->store?->name ?? 'Toko',
            'price' => $price,
            'badge' => $badge,
            'location' => $location,
            'sold' => $product->stock ? "Terjual {$product->stock}" : 'Terjual 0',
            'tags' => collect([
                $product->is_pdn ? 'PDN' : null,
                $product->is_pkp ? 'PKP' : null,
                $product->is_tkdn ? 'TKDN' : null,
            ])->filter()->values(),
            'image' => $this->resolveProductImage($product),
        ];
    }

    private function resolveProductImage($product): ?string
    {
        $fromMedia = $product->getFirstMediaUrl('product_image');

        if ($fromMedia) {
            return $this->formatMediaUrl($fromMedia);
        }

        if ($product->image_url) {
            return $product->image_url;
        }

        return "https://picsum.photos/seed/{$product->slug}/800/400";
    }
}
