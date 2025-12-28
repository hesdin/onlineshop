<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $banners = Banner::query()
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->ordered()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Banner $banner) => [
                'id' => $banner->id,
                'title' => $banner->title,
                'type' => $banner->type,
                'type_label' => $banner->type === 'hero_slider' ? 'Hero Slider' : 'Hero Promo',
                'position' => $banner->position,
                'is_active' => (bool) $banner->is_active,
                'image_url' => $banner->image_url,
                'created_at' => $banner->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Banners/Index', [
            'banners' => $banners,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Banners/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:500',
            'type' => ['required', Rule::in(['hero_slider', 'hero_promo'])],
            'position' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'image' => 'required|image|max:2048',
        ]);

        $banner = Banner::create([
            'title' => $validated['title'],
            'alt_text' => $validated['alt_text'] ?? null,
            'url' => $validated['url'] ?? null,
            'type' => $validated['type'],
            'position' => $validated['position'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('banner_image');
        }

        return Redirect::route('admin.banners.index')->with('success', 'Banner berhasil dibuat.');
    }

    public function edit(Banner $banner): Response
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => [
                'id' => $banner->id,
                'title' => $banner->title,
                'alt_text' => $banner->alt_text,
                'url' => $banner->url,
                'type' => $banner->type,
                'position' => $banner->position,
                'is_active' => (bool) $banner->is_active,
                'starts_at' => $banner->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $banner->ends_at?->format('Y-m-d\TH:i'),
                'image_url' => $banner->image_url,
            ],
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:500',
            'type' => ['required', Rule::in(['hero_slider', 'hero_promo'])],
            'position' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'image' => 'nullable|image|max:2048',
        ]);

        $banner->update([
            'title' => $validated['title'],
            'alt_text' => $validated['alt_text'] ?? null,
            'url' => $validated['url'] ?? null,
            'type' => $validated['type'],
            'position' => $validated['position'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')->toMediaCollection('banner_image');
        }

        return Redirect::route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return Redirect::route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }
}
