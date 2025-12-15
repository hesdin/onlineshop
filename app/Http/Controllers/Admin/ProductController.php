<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $store = $request->input('store');
        $category = $request->input('category');

        $storeId = is_numeric($store) ? (int) $store : null;
        $categoryId = is_numeric($category) ? (int) $category : null;

        $products = Product::with(['store:id,name', 'category:id,name'])
            ->when($search, fn ($query) => $query->search($search))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'brand' => $product->brand,
                'description' => $product->description,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'min_order' => $product->min_order,
                'stock' => $product->stock,
                'weight' => $product->weight,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
                'item_type' => $product->item_type,
                'status' => $product->status,
                'location_city' => $product->location_city,
                'location_province' => $product->location_province,
                'location_province_id' => $product->location_province_id,
                'location_city_id' => $product->location_city_id,
                'location_district_id' => $product->location_district_id,
                'location_postal_code' => $product->location_postal_code,
                'is_pdn' => $product->is_pdn,
                'store' => $product->store ? [
                    'id' => $product->store->id,
                    'name' => $product->store->name,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'created_at' => $product->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            ...$this->formOptions(),
            'filters' => [
                'search' => $search,
                'status' => $status ?: null,
                'store' => $storeId ? (string) $storeId : null,
                'category' => $categoryId ? (string) $categoryId : null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', $this->formOptions());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::create($data);

        if ($request->hasFile('images')) {
            $product->clearMediaCollection('product_image');
            foreach ($request->file('images', []) as $image) {
                $product->addMedia($image)->toMediaCollection('product_image');
            }
        }

        return Redirect::route('admin.products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $this->formatProduct($product),
            ...$this->formOptions(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $product->update($data);

        if ($request->hasFile('images')) {
            $product->clearMediaCollection('product_image');
            foreach ($request->file('images', []) as $image) {
                $product->addMedia($image)->toMediaCollection('product_image');
            }
        }

        return Redirect::route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return Redirect::route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function formOptions(): array
    {
        $storeOptions = Store::with(['provinceRegion', 'cityRegion', 'districtRegion'])
            ->orderBy('name')
            ->get(['id', 'name', 'province_id', 'city_id', 'district_id', 'postal_code'])
            ->map(fn ($storeOption) => [
                'id' => $storeOption->id,
                'name' => $storeOption->name,
                'city' => $storeOption->city,
                'province' => $storeOption->province,
                'district' => $storeOption->district,
                'postal_code' => $storeOption->postal_code,
            ]);

        $categoryOptions = Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($categoryOption) => [
                'id' => $categoryOption->id,
                'name' => $categoryOption->name,
            ]);

        return [
            'storeOptions' => $storeOptions,
            'categoryOptions' => $categoryOptions,
            'statuses' => Product::statuses(),
            'itemTypes' => Product::itemTypes(),
        ];
    }

    private function formatProduct(Product $product): array
    {
        $product->loadMissing(['store:id,name', 'category:id,name']);

        return [
            'id' => $product->id,
            'store_id' => $product->store_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $product->brand,
            'description' => $product->description,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'min_order' => $product->min_order,
            'stock' => $product->stock,
            'weight' => $product->weight,
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'item_type' => $product->item_type,
            'status' => $product->status,
            'location_city' => $product->location_city,
            'location_province' => $product->location_province,
            'location_province_id' => $product->location_province_id,
            'location_city_id' => $product->location_city_id,
            'location_district_id' => $product->location_district_id,
            'location_postal_code' => $product->location_postal_code,
            'is_pdn' => $product->is_pdn,
            'store' => $product->store ? [
                'id' => $product->store->id,
                'name' => $product->store->name,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
        ];
    }
}
