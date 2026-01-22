<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * List all categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('children');

        // Option to get only parent categories
        if ($request->boolean('parents_only')) {
            $query->whereNull('parent_id');
        }

        // Option to include children
        if ($request->boolean('with_children')) {
            $query->with('children');
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $categories = $query->orderBy('name')->get();

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Get products by category
     */
    public function products(string $slug, Request $request): AnonymousResourceCollection
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // Get category and its children IDs
        $categoryIds = [$category->id];
        $childIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();
        $categoryIds = array_merge($categoryIds, $childIds);

        $query = Product::query()
            ->with(['store', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', '!=', Product::STATUS_INACTIVE);

        // Search within category
        if ($request->filled('search')) {
            $query->search($request->search);
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
                'category' => new CategoryResource($category),
            ]);
    }
}
