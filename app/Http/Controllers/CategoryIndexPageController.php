<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class CategoryIndexPageController extends Controller
{
    public function __invoke(): Response
    {
        $alphabet = range('A', 'Z');

        $categories = Category::query()
            ->with(['children:id,name,slug,parent_id'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $categorized = $categories
            ->map(function (Category $category) {
                $letter = mb_strtoupper(mb_substr($category->name ?? '', 0, 1)) ?: '#';

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'letter' => $letter ?: '#',
                    'children' => $category->children
                        ->sortBy('name')
                        ->map(fn ($child) => [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ])
                        ->values(),
                ];
            })
            ->groupBy('letter');

        $groups = collect($alphabet)
            ->mapWithKeys(fn ($letter) => [$letter => [
                'letter' => $letter,
                'categories' => $categorized->get($letter)?->values() ?? collect(),
            ]]);

        $others = $categorized->except($alphabet);
        if ($others->isNotEmpty()) {
            $groups->put('#', [
                'letter' => '#',
                'categories' => $others->flatten(1)->values(),
            ]);
        }

        return Inertia::render('Categories/Index', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'groups' => $groups->values(),
        ]);
    }
}
