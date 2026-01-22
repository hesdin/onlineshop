<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CollectionResource;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Get collection detail with products
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $collection = Collection::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Load products with pagination
        $perPage = min($request->get('per_page', 20), 50);

        $products = $collection->products()
            ->where('status', '!=', Product::STATUS_INACTIVE)
            ->with(['store', 'category'])
            ->paginate($perPage);

        return response()->json([
            'collection' => new CollectionResource($collection),
            'products' => [
                'data' => \App\Http\Resources\Api\V1\ProductListResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ],
        ]);
    }
}
