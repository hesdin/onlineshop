<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                        'avatar_url' => $this->getUserAvatarUrl($request->user()),
                        'buyer_type_label' => $this->getBuyerTypeLabel($request->user()),
                        'store' => $this->getStoreData($request),
                    ]
                    : null,
                'seller_document' => $this->getSellerDocumentData($request),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cart' => fn () => $this->cartData($request),
            'megaMenu' => fn () => $this->megaMenuData(),
            'unread_chat_count' => fn () => $this->getUnreadChatCount($request),
            'csrf_token' => csrf_token(),
            'recaptcha' => [
                'siteKey' => config('recaptchav3.sitekey', ''),
            ],
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

    protected function getStoreData(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        // Check if user has a store (seller role)
        $store = $user->store;

        if (! $store) {
            return null;
        }

        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'logo_url' => $store->logo_url,
        ];
    }

    protected function getSellerDocumentData(Request $request): ?array
    {
        $user = $request->user();

        if (! $user || ! $user->store) {
            return null;
        }

        $sellerDocument = $user->store->sellerDocument;

        if (! $sellerDocument) {
            return [
                'exists' => false,
                'submission_status' => 'draft',
                'is_approved' => false,
                'is_submitted' => false,
                'is_rejected' => false,
                'documents_uploaded' => 0,
                'admin_notes' => null,
            ];
        }

        // Count uploaded required documents
        $documentsUploaded = 0;
        if ($sellerDocument->getFirstMedia('ktp')) {
            $documentsUploaded++;
        }
        if ($sellerDocument->getFirstMedia('npwp')) {
            $documentsUploaded++;
        }
        if ($sellerDocument->getFirstMedia('nib')) {
            $documentsUploaded++;
        }

        return [
            'exists' => true,
            'submission_status' => $sellerDocument->submission_status ?? 'draft',
            'is_approved' => $sellerDocument->isApproved(),
            'is_submitted' => $sellerDocument->isPending(),
            'is_rejected' => $sellerDocument->isRejected(),
            'documents_uploaded' => $documentsUploaded,
            'admin_notes' => $sellerDocument->admin_notes,
        ];
    }

    protected function getUserAvatarUrl($user): ?string
    {
        if (! $user) {
            return null;
        }

        $media = $user->getFirstMedia('profile_image');
        if (! $media) {
            return null;
        }

        // Return relative URL for consistency
        return '/storage/'.$media->id.'/'.$media->file_name;
    }

    protected function getUnreadChatCount(Request $request): int
    {
        $user = $request->user();

        if (! $user || ! $user->store) {
            return 0;
        }

        return \App\Models\Message::whereHas('conversation', function ($q) use ($user) {
            $q->where('store_id', $user->store->id);
        })
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->count();
    }

    protected function getBuyerTypeLabel($user): string
    {
        if (! $user) {
            return 'Guest';
        }

        // Check roles to determine buyer type
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('seller')) {
                return 'Penjual';
            }
            if ($user->hasRole('admin')) {
                return 'Administrator';
            }
        }

        // Default for customers
        // return 'Buyer Retail';
        return 'customer';
    }
}
