<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerProductRequest;
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
    public function index(Request $request): Response|RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $category = $request->input('category');
        $categoryId = is_numeric($category) ? (int) $category : null;

        $products = Product::where('store_id', $store->id)
            ->with('category:id,name')
            ->when($search, fn ($query) => $query->search($search))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => $this->formatProduct($product));

        $categoryOptions = Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($categoryOption) => [
                'id' => $categoryOption->id,
                'name' => $categoryOption->name,
            ]);

        return Inertia::render('Seller/Products/Index', [
            'products' => $products,
            'categoryOptions' => $categoryOptions,
            'statuses' => Product::statuses(),
            'itemTypes' => Product::itemTypes(),
            'filters' => [
                'search' => $search,
                'status' => $status ?: null,
                'category' => $categoryId ? (string) $categoryId : null,
            ],
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        // Restriction: Cannot create product if document not approved
        $sellerDocument = $store->sellerDocument;
        if (! $sellerDocument || ! $sellerDocument->isApproved()) {
             return Redirect::route('seller.documents.show')->with('error', 'Silakan lengkapi dan verifikasi dokumen toko untuk menambah produk.');
        }

        return Inertia::render('Seller/Products/Create', [
            'categoryOptions' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($categoryOption) => [
                    'id' => $categoryOption->id,
                    'name' => $categoryOption->name,
                ]),
            'statuses' => Product::statuses(),
            'itemTypes' => Product::itemTypes(),
            'visibilityOptions' => Product::visibilityScopes(),
            'shippingMethods' => Product::shippingMethods(),
            'storeLocation' => $this->storeLocationPayload($store),
        ]);
    }

    public function store(SellerProductRequest $request): RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        // Restriction: Cannot create product if document not approved
        $sellerDocument = $store->sellerDocument;
        if (! $sellerDocument || ! $sellerDocument->isApproved()) {
             return Redirect::back()->with('error', 'Silakan lengkapi dan verifikasi dokumen toko untuk menambah produk.');
        }

        $data = $request->validated();
        $data['store_id'] = $store->id;

        $product = Product::create($data);

        if ($request->hasFile('images')) {
            $product->clearMediaCollection('product_image');
            foreach ($request->file('images', []) as $image) {
                $product->addMedia($image)->toMediaCollection('product_image');
            }
        }

        return Redirect::route('seller.products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Request $request, Product $product): Response|RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        abort_if($product->store_id !== $store->id, 403);

        return Inertia::render('Seller/Products/Edit', [
            'product' => $this->formatProduct($product),
            'categoryOptions' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($categoryOption) => [
                    'id' => $categoryOption->id,
                    'name' => $categoryOption->name,
                ]),
            'statuses' => Product::statuses(),
            'itemTypes' => Product::itemTypes(),
            'visibilityOptions' => Product::visibilityScopes(),
            'shippingMethods' => Product::shippingMethods(),
            'storeLocation' => $this->storeLocationPayload($store),
        ]);
    }

    public function update(SellerProductRequest $request, Product $product): RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        abort_if($product->store_id !== $store->id, 403);

        $sellerDocument = $store->sellerDocument;
        if ($request->input('status') === 'ready' && (! $sellerDocument || ! $sellerDocument->isApproved())) {
             return Redirect::back()->with('error', 'Verifikasi dokumen diperlukan untuk mempublikasikan produk.');
        }

        // Debug: Log raw request data
        \Log::info('Raw request data for product update', [
            'product_id' => $product->id,
            'shipping_pickup_raw' => $request->input('shipping_pickup'),
            'shipping_delivery_raw' => $request->input('shipping_delivery'),
            'all_input' => $request->only(['shipping_pickup', 'shipping_delivery', 'is_pdn']),
        ]);

        $data = $request->validated();

        // Debug logging - remove after fix
        \Log::info('Product update data', [
            'product_id' => $product->id,
            'is_pdn' => $data['is_pdn'] ?? 'NOT SET',
            'shipping_pickup' => $data['shipping_pickup'] ?? 'NOT SET',
            'shipping_delivery' => $data['shipping_delivery'] ?? 'NOT SET',
            'all_data_keys' => array_keys($data),
        ]);

        $product->update($data);

        if ($request->filled('deleted_images')) {
            $deletedIds = $request->input('deleted_images', []);
            $product->media()->whereIn('id', $deletedIds)->delete();
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images', []) as $image) {
                $product->addMedia($image)->toMediaCollection('product_image');
            }
        }

        return Redirect::route('seller.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        abort_if($product->store_id !== $store->id, 403);

        $product->delete();

        return Redirect::route('seller.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function getStoreOrRedirect(Request $request): Store|RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (! $store) {
            return Redirect::route('seller.settings.edit')->with('error', 'Lengkapi profil toko terlebih dahulu.');
        }

        return $store;
    }

    private function formatProduct(Product $product): array
    {
        $product->loadMissing('category:id,name');

        return [
            'id' => $product->id,
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
            'visibility_scope' => $product->visibility_scope,
            'location_city' => $product->location_city,
            'location_province' => $product->location_province,
            'location_province_id' => $product->location_province_id,
            'location_city_id' => $product->location_city_id,
            'location_district_id' => $product->location_district_id,
            'location_postal_code' => $product->location_postal_code,
            'is_pdn' => $product->is_pdn,
            'shipping_pickup' => $product->shipping_pickup,
            'shipping_delivery' => $product->shipping_delivery,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'image_url' => $product->image_url,
            'images' => $product->getMedia('product_image')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $product->normalizeMediaUrl($media->getUrl()),
            ])->values()->toArray(),
            'created_at' => $product->created_at?->toDateTimeString(),
        ];
    }

    private function storeLocationPayload(Store $store): array
    {
        return [
            'province_id' => $store->province_id,
            'city_id' => $store->city_id,
            'district_id' => $store->district_id,
            'postal_code' => $store->postal_code,
            'province_name' => $store->province,
            'city_name' => $store->city,
            'district_name' => $store->district,
        ];
    }
}
