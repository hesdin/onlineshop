<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BannerResource;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\CollectionResource;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Get home page data
     */
    public function index(): JsonResponse
    {
        // Hero banners (slider)
        $heroSliders = Banner::active()
            ->heroSlider()
            ->ordered()
            ->limit(5)
            ->get();

        // Promo banners
        $heroPromos = Banner::active()
            ->heroPromo()
            ->ordered()
            ->limit(4)
            ->get();

        // Categories (parent only with children)
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->limit(12)
            ->get();

        // Active collections
        $collections = Collection::where('is_active', true)
            ->with(['products' => function ($query) {
                $query->where('status', '!=', Product::STATUS_INACTIVE)
                    ->limit(8);
            }])
            ->limit(4)
            ->get();

        // Featured products (latest active products)
        $featuredProducts = Product::with(['store', 'category'])
            ->where('status', '!=', Product::STATUS_INACTIVE)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        // Sale products (products with sale_price)
        $saleProducts = Product::with(['store', 'category'])
            ->where('status', '!=', Product::STATUS_INACTIVE)
            ->whereNotNull('sale_price')
            ->where('sale_price', '<', \DB::raw('price'))
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return response()->json([
            'hero_sliders' => BannerResource::collection($heroSliders),
            'hero_promos' => BannerResource::collection($heroPromos),
            'categories' => CategoryResource::collection($categories),
            'collections' => CollectionResource::collection($collections),
            'featured_products' => ProductListResource::collection($featuredProducts),
            'sale_products' => ProductListResource::collection($saleProducts),
        ]);
    }
}
