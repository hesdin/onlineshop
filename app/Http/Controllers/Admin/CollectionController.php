<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    private function formatMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base ?: Str::random(8);

        $query = Collection::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if (! $query->exists()) {
            return $slug;
        }

        $suffix = 2;
        while (true) {
            $candidate = "{$slug}-{$suffix}";
            $candidateQuery = Collection::query()->where('slug', $candidate);
            if ($ignoreId) {
                $candidateQuery->where('id', '!=', $ignoreId);
            }
            if (! $candidateQuery->exists()) {
                return $candidate;
            }
            $suffix++;
        }
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $collections = Collection::query()
            ->withCount('products')
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Collection $collection) => [
                'id' => $collection->id,
                'title' => $collection->title,
                'slug' => $collection->slug,
                'is_active' => $collection->is_active,
                'display_mode' => $collection->display_mode,
                'products_count' => $collection->products_count,
                'home_image_url' => $this->formatMediaUrl($collection->getFirstMediaUrl('home_image')),
                'cover_image_url' => $this->formatMediaUrl($collection->getFirstMediaUrl('cover_image')),
                'color_theme' => $collection->color_theme,
                'created_at' => $collection->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Collections/Index', [
            'collections' => $collections,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Collections/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color_theme' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_mode' => 'nullable|string|in:slider,image_only',
            'home_image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $collection = Collection::create([
            'title' => $request->title,
            'slug' => $this->generateUniqueSlug($request->title),
            'description' => $request->description ?: null,
            'color_theme' => $request->input('color_theme') ?: null,
            'is_active' => $request->boolean('is_active', true),
            'display_mode' => $request->input('display_mode', Collection::DISPLAY_MODE_SLIDER),
        ]);

        if ($request->hasFile('home_image')) {
            $collection->addMediaFromRequest('home_image')->toMediaCollection('home_image');
        }

        if ($request->hasFile('cover_image')) {
            $collection->addMediaFromRequest('cover_image')->toMediaCollection('cover_image');
        }

        $productIds = [];
        if ($collection->display_mode !== Collection::DISPLAY_MODE_IMAGE_ONLY) {
            $productIds = collect($request->input('product_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if (! empty($productIds)) {
            $collection->products()->sync($productIds);
        }

        return Redirect::route('admin.collections.index')->with('success', 'Koleksi berhasil dibuat.');
    }

    public function edit(Request $request, Collection $collection): Response
    {
        $productSearch = $request->string('product_search')->toString();

        $products = Product::query()
            ->select(['id', 'name'])
            ->when($productSearch, fn ($query) => $query->where('name', 'like', "%{$productSearch}%"))
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
            ])
            ->values();

        return Inertia::render('Admin/Collections/Edit', [
            'collection' => [
                'id' => $collection->id,
                'title' => $collection->title,
                'description' => $collection->description,
                'color_theme' => $collection->color_theme,
                'is_active' => $collection->is_active,
                'display_mode' => $collection->display_mode,
                'home_image_url' => $this->formatMediaUrl($collection->getFirstMediaUrl('home_image')),
                'cover_image_url' => $this->formatMediaUrl($collection->getFirstMediaUrl('cover_image')),
                'product_ids' => $collection->products()->pluck('products.id')->values(),
                'selected_products' => $collection->products()
                    ->select(['products.id', 'products.name'])
                    ->orderBy('products.name')
                    ->limit(100)
                    ->get()
                    ->map(fn (Product $product) => ['id' => $product->id, 'name' => $product->name])
                    ->values(),
            ],
            'products' => $products,
            'filters' => [
                'product_search' => $productSearch,
            ],
        ]);
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color_theme' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_mode' => 'nullable|string|in:slider,image_only',
            'home_image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $collection->update([
            'title' => $request->title,
            'slug' => $this->generateUniqueSlug($request->title, $collection->id),
            'description' => $request->description ?: null,
            'color_theme' => $request->input('color_theme') ?: null,
            'is_active' => $request->boolean('is_active'),
            'display_mode' => $request->input('display_mode', Collection::DISPLAY_MODE_SLIDER),
        ]);

        if ($request->hasFile('home_image')) {
            $collection->addMediaFromRequest('home_image')->toMediaCollection('home_image');
        }

        if ($request->hasFile('cover_image')) {
            $collection->addMediaFromRequest('cover_image')->toMediaCollection('cover_image');
        }

        $productIds = [];
        if ($collection->display_mode !== Collection::DISPLAY_MODE_IMAGE_ONLY) {
            $productIds = collect($request->input('product_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $collection->products()->sync($productIds);

        return Redirect::route('admin.collections.index')->with('success', 'Koleksi berhasil diperbarui.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        $collection->delete();

        return Redirect::route('admin.collections.index')->with('success', 'Koleksi berhasil dihapus.');
    }
}
