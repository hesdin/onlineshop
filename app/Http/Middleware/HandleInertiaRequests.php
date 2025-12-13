<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'roles' => method_exists($request->user(), 'getRoleNames')
                            ? $request->user()->getRoleNames()->toArray()
                            : [],
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cart' => fn () => $this->cartData($request),
            'megaMenu' => fn () => $this->megaMenuData(),
            'csrf_token' => csrf_token(),
        ];
    }

    protected function cartData(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [
                'items' => [],
                'items_count' => 0,
                'total' => 0,
                'updated_at' => now()->valueOf(),
            ];
        }

        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->where('status', Cart::STATUS_OPEN)
            ->with(['items.product' => function ($query) {
                $query->select('id', 'name', 'slug', 'price', 'sale_price', 'store_id');
            }])
            ->first();

        if (! $cart) {
            return [
                'items' => [],
                'items_count' => 0,
                'total' => 0,
                'updated_at' => now()->valueOf(),
            ];
        }

        $items = $cart->items->map(function ($item) {
            $product = $item->product;
            $unitPrice = $item->unit_price ?? $product?->sale_price ?? $product?->price ?? 0;
            $subtotal = $item->subtotal ?: $unitPrice * $item->quantity;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $product?->name,
                'qty' => $item->quantity,
                'unit_price' => (int) $unitPrice,
                'subtotal' => (int) $subtotal,
                'image' => $product?->image_url ?: "https://picsum.photos/seed/cart-{$item->product_id}/200/200",
                'url' => $product ? route('product.detail', ['slug' => $product->slug, 'product' => $product]) : null,
            ];
        })->values();

        return [
            'items' => $items,
            'items_count' => (int) $items->sum('qty'),
            'total' => (int) $items->sum('subtotal'),
            'updated_at' => now()->valueOf(),
        ];
    }

    protected function megaMenuData(): array
    {
        return Cache::remember('mega_menu_data', 600, function () {
            return Category::query()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get()
                ->map(function (Category $category) {
                    $children = $category->children
                        ->map(fn (Category $child) => [
                            'label' => $child->name,
                            'slug' => $child->slug,
                            'url' => route('category.show', ['category' => $child->slug]),
                        ])
                        ->values();

                    $chunkSize = max(1, (int) ceil($children->count() / 4));

                    $columns = $children->isNotEmpty()
                        ? $children
                            ->chunk($chunkSize)
                            ->map(fn ($chunk) => $chunk->values()->all())
                            ->values()
                            ->all()
                        : [[[
                            'label' => $category->name,
                            'slug' => $category->slug,
                            'url' => route('category.show', ['category' => $category->slug]),
                        ]]];

                    return [
                        'label' => $category->name,
                        'slug' => $category->slug,
                        'url' => route('category.show', ['category' => $category->slug]),
                        'columns' => $columns,
                    ];
                })
                ->values()
                ->all();
        });
    }
}
