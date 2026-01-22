<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Http\Resources\Api\V1\StoreResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    /**
     * Get store detail
     */
    public function show(string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)
            ->withCount('products')
            ->firstOrFail();

        return response()->json([
            'store' => new StoreResource($store),
        ]);
    }

    /**
     * Get store products
     */
    public function products(string $slug, Request $request): AnonymousResourceCollection
    {
        $store = Store::where('slug', $slug)->firstOrFail();

        $query = Product::query()
            ->with(['category'])
            ->where('store_id', $store->id)
            ->where('status', '!=', Product::STATUS_INACTIVE);

        // Search within store
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['created_at', 'price', 'name'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(sale_price, price) ' . ($sortOrder === 'asc' ? 'ASC' : 'DESC'));
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = min($request->get('per_page', 20), 50);
        $products = $query->paginate($perPage);

        return ProductListResource::collection($products)
            ->additional([
                'store' => new StoreResource($store),
            ]);
    }
}
