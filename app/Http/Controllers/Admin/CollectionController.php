<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
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
                'products_count' => $collection->products_count,
                'cover_image_url' => $collection->getFirstMediaUrl('cover_image'),
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $collection = Collection::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->hasFile('cover_image')) {
            $collection->addMediaFromRequest('cover_image')->toMediaCollection('cover_image');
        }

        return Redirect::route('admin.collections.index')->with('success', 'Koleksi berhasil dibuat.');
    }

    public function edit(Collection $collection): Response
    {
        return Inertia::render('Admin/Collections/Edit', [
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'description' => $collection->description,
                'is_active' => $collection->is_active,
                'cover_image_url' => $collection->getFirstMediaUrl('cover_image'),
            ],
        ]);
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $collection->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('cover_image')) {
            $collection->addMediaFromRequest('cover_image')->toMediaCollection('cover_image');
        }

        return Redirect::route('admin.collections.index')->with('success', 'Koleksi berhasil diperbarui.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        $collection->delete();

        return Redirect::route('admin.collections.index')->with('success', 'Koleksi berhasil dihapus.');
    }
}
