<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use App\Support\CustomerLocationResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $cityId = CustomerLocationResolver::resolveCityId($request);

        $cart = Cart::query()
            ->open()
            ->where('user_id', $user->id)
            ->with([
                'items.product' => function ($query) {
                    $query->select([
                        'id',
                        'name',
                        'slug',
                        'price',
                        'sale_price',
                        'stock',
                        'min_order',
                        'location_city_id',
                        'location_province_id',
                        'location_district_id',
                        'location_postal_code',
                        'is_pdn',
                        'store_id',
                    ])->with([
                        'store:id,name,is_umkm,tax_status,bumn_partner,province_id,city_id,district_id',
                        'store.provinceRegion:id,name',
                        'store.cityRegion:id,name',
                    ]);
                },
            ])
            ->first();

        $groups = $cart ? $this->groupCartItems($cart) : [];

        return Inertia::render('Cart', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'groups' => $groups,
            'recommendations' => $this->recommendations($user?->id, $cityId),
            'paymentMethods' => $this->paymentMethods(),
            'customerAddress' => $this->customerAddress($request),
        ]);
    }

    protected function groupCartItems(Cart $cart): array
    {
        return $cart->items
            ->groupBy('store_id')
            ->map(function ($items, $storeId) {
                $product = $items->first()->product;
                $store = $product?->store;
                $location = $this->formatLocation($product) ?: $this->formatStoreLocation($store);

                return [
                    'storeId' => $storeId,
                    'vendor' => $store?->name ?? 'Toko',
                    'location' => $location,
                    'benefit' => $this->storeBenefit($store),
                    'items' => $items->map(function ($item) use ($location) {
                        $product = $item->product;
                        $price = $item->unit_price ?? $product?->sale_price ?? $product?->price ?? 0;
                        $stock = $product?->stock;

                        return [
                            'id' => $item->id,
                            'productId' => $product?->id,
                            'name' => $product?->name,
                            'price' => (int) $price,
                            'qty' => (int) $item->quantity,
                            'subtotal' => (int) ($item->subtotal ?: $price * $item->quantity),
                            'img' => $product?->image_url ?: "https://picsum.photos/seed/cart-{$item->id}/400/400",
                            'tags' => $this->productTags($product),
                            'status' => $stock === null || $stock > 0 ? 'Tersedia' : 'Stok habis',
                            'location' => $location,
                            'url' => $product ? route('product.detail', ['slug' => $product->slug, 'product' => $product]) : null,
                            'stock' => $stock,
                            'minOrder' => $product?->min_order ?: 1,
                            'shipping_method' => $item->shipping_method,
                        ];
                    })->values(),
                ];
            })
            ->values()
            ->all();
    }

    protected function recommendations(?int $userId, ?int $cityId): array
    {
        return Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'price',
                'sale_price',
                'location_city_id',
                'location_province_id',
                'is_pdn',
                'store_id',
            ])
            ->visibleForCity($cityId)
            ->latest()
            ->limit(12)
            ->with('store:id,is_umkm')
            ->get()
            ->map(function (Product $product) {
                $price = $product->sale_price ?? $product->price ?? 0;
                $originalPrice = $product->sale_price ? $product->price : null;
                $discountPercent = ($originalPrice && $originalPrice > $price)
                    ? (int) round((($originalPrice - $price) / $originalPrice) * 100)
                    : null;

                return [
                    'id' => $product->id,
                    'title' => $product->name,
                    'price' => $price,
                    'originalPrice' => $originalPrice,
                    'discountPercent' => $discountPercent,
                    'location' => $this->formatLocation($product),
                    'sold' => null,
                    'img' => $product->image_url,
                    'url' => route('product.detail', ['slug' => $product->slug, 'product' => $product]),
                    'badges' => $this->productTags($product),
                    'storeBadge' => $product->store?->is_umkm ? 'UMKM' : null,
                ];
            })
            ->values()
            ->all();
    }

    protected function storeBenefit($store): ?string
    {
        if (! $store) {
            return null;
        }

        if ($store->bumn_partner) {
            return 'BUMN Pengampu';
        }

        if ($store->is_umkm) {
            return 'UMKM';
        }

        return $store->tax_status ?: 'Toko';
    }

    protected function productTags(?Product $product): array
    {
        if (! $product) {
            return [];
        }

        return collect([
            $product->is_pdn ? 'PDN' : null,
        ])
            ->filter()
            ->values()
            ->all();
    }

    protected function formatLocation(?Product $product): ?string
    {
        if (! $product) {
            return null;
        }

        $city = $product->location_city;
        $province = $product->location_province;

        if ($city || $province) {
            return trim(($city ? "{$city}, " : '').($province ?? ''), ', ');
        }

        return null;
    }

    protected function formatStoreLocation($store): ?string
    {
        if (! $store) {
            return null;
        }

        $city = $store->city;
        $province = $store->province;

        if ($city || $province) {
            return trim(($city ? "{$city}, " : '').($province ?? ''), ', ');
        }

        return null;
    }

    protected function paymentMethods(): array
    {
        return [
            'mandiri',
            'BNI',
            'BSI',
            'BTN',
            'BCA',
            'BRI',
            'POSPAY',
            'LINKAJA',
            'DANA',
            'OVO',
            'VISA',
            'MASTER',
        ];
    }

    protected function customerAddress(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $address = Address::query()
            ->where('user_id', $user->id)
            ->with(['provinceRegion:id,name', 'cityRegion:id,name', 'districtRegion:id,name'])
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->first();

        if (! $address) {
            return null;
        }

        return $this->transformAddress($address, $user->name);
    }

    protected function transformAddress(Address $address, ?string $fallbackRecipientName = null): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label ?: 'Alamat Utama',
            'recipient_name' => $address->recipient_name ?: $fallbackRecipientName,
            'phone' => $address->phone,
            'province_id' => $address->province_id,
            'city_id' => $address->city_id,
            'district_id' => $address->district_id,
            'postal_code' => $address->postal_code,
            'address_line' => $address->address_line,
            'is_default' => (bool) $address->is_default,
            'note' => $address->note,
            'district' => $address->district,
            'city' => $address->city,
            'province' => $address->province,
        ];
    }
}
