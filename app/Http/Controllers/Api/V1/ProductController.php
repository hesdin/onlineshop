<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * List products with pagination, filters, and search
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->with(['store', 'category'])
            ->where('status', '!=', Product::STATUS_INACTIVE);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by store
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_price', '>=', $request->min_price)
                    ->orWhere(function ($q2) use ($request) {
                        $q2->whereNull('sale_price')
                            ->where('price', '>=', $request->min_price);
                    });
            });
        }

        if ($request->filled('max_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_price', '<=', $request->max_price)
                    ->orWhere(function ($q2) use ($request) {
                        $q2->whereNull('sale_price')
                            ->where('price', '<=', $request->max_price);
                    });
            });
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by location (city-based visibility)
        if ($request->filled('city_id')) {
            $query->visibleForCity($request->integer('city_id'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['created_at', 'price', 'name', 'stock'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(sale_price, price) ' . ($sortOrder === 'asc' ? 'ASC' : 'DESC'));
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = min($request->get('per_page', 20), 50);
        $products = $query->paginate($perPage);

        return ProductListResource::collection($products);
    }

    /**
     * Get product detail
     */
    public function show(Product $product): JsonResponse
    {
        if ($product->status === Product::STATUS_INACTIVE) {
            return response()->json([
                'message' => 'Produk tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        $product->load([
            'store',
            'category.parent',
            'locationProvince',
            'locationCity',
            'locationDistrict',
        ]);

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }

    /**
     * Get product reviews
     */
    public function reviews(Product $product, Request $request): AnonymousResourceCollection
    {
        $reviews = Review::where('product_id', $product->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return ReviewResource::collection($reviews);
    }
}
