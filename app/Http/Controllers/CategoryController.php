<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $categories = Category::with('parent')
            ->when($search, fn ($query) => $query->search($search))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                ] : null,
                'image_url' => $this->formatMediaUrl(
                    $category->getFirstMediaUrl('category_image', 'thumb')
                        ?: $category->getFirstMediaUrl('category_image')
                ),
                'created_at' => $category->created_at?->toDateTimeString(),
            ]);

        $parentOptions = Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name]);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'parentOptions' => $parentOptions,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    private function formatMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $category = Category::create($data);

        if ($request->hasFile('image')) {
            $category
                ->addMediaFromRequest('image')
                ->toMediaCollection('category_image');
        }

        return Redirect::route('categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update($data);

        if ($request->hasFile('image')) {
            $category
                ->addMediaFromRequest('image')
                ->toMediaCollection('category_image');
        }

        return Redirect::route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return Redirect::route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
