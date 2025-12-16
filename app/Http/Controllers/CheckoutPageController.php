<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $selectedItems = collect(Arr::wrap($request->input('items')))
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->filter();

        $cart = Cart::query()
            ->open()
            ->where('user_id', $user->id)
            ->with([
                'items' => function ($query) use ($selectedItems) {
                    if ($selectedItems->isNotEmpty()) {
                        $query->whereIn('id', $selectedItems);
                    }
                },
                'items.product' => function ($query) {
                    $query->select([
                        'id',
                        'name',
                        'slug',
                        'price',
                        'sale_price',
                        'stock',
                        'min_order',
                        'weight',
                        'item_type',
                        'location_city_id',
                        'location_province_id',
                        'location_district_id',
                        'location_postal_code',
                        'is_pdn',
                        'store_id',
                        'shipping_pickup',
                        'shipping_delivery',
                    ])->with([
                        'store:id,name,is_umkm,tax_status,bumn_partner,province_id,city_id,district_id',
                        'store.provinceRegion:id,name',
                        'store.cityRegion:id,name',
                    ]);
                },
            ])
            ->first();

        $groups = $cart ? $this->groupCartItems($cart) : [];
        $addresses = $this->addresses($user->id);

        return Inertia::render('Checkout', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'groups' => $groups,
            'addresses' => $addresses,
            'shippingOptions' => $this->shippingOptions(),
            'noteLimit' => 100,
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
                        $weight = $product?->weight ? (int) $product->weight : null;
                        $type = $product?->item_type === Product::ITEM_TYPE_SERVICE ? 'Jasa' : 'Barang';

                        return [
                            'id' => $item->id,
                            'productId' => $product?->id,
                            'name' => $product?->name,
                            'note' => $item->note,
                            'price' => (int) $price,
                            'qty' => (int) $item->quantity,
                            'subtotal' => (int) ($item->subtotal ?: $price * $item->quantity),
                            'img' => $product?->image_url ?: "https://picsum.photos/seed/checkout-{$item->id}/400/400",
                            'tags' => $this->productTags($product),
                            'status' => $stock === null || $stock > 0 ? 'Tersedia' : 'Stok habis',
                            'location' => $location,
                            'url' => $product ? route('product.detail', ['slug' => $product->slug, 'product' => $product]) : null,
                            'stock' => $stock,
                            'minOrder' => $product?->min_order ?: 1,
                            'type' => $type,
                            'weight' => $weight,
                            'shipping_method' => $item->shipping_method,
                            'shipping_pickup' => (bool) $product?->shipping_pickup,
                            'shipping_delivery' => (bool) $product?->shipping_delivery,
                        ];
                    })->values(),
                ];
            })
            ->values()
            ->all();
    }

    protected function addresses(int $userId): array
    {
        return Address::query()
            ->where('user_id', $userId)
            ->with(['provinceRegion:id,name', 'cityRegion:id,name', 'districtRegion:id,name'])
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get()
            ->map(function (Address $address) {
                $detail = implode(', ', array_filter([
                    $address->address_line,
                    $address->district,
                    $address->city,
                    $address->province,
                    $address->postal_code,
                ]));

                return [
                    'id' => $address->id,
                    'name' => $address->recipient_name ?: $address->label,
                    'label' => $address->label ?: 'Alamat',
                    'phone' => $address->phone,
                    'detail' => $detail,
                    'tag' => $address->label ?: 'Alamat',
                    'is_default' => (bool) $address->is_default,
                    'recipient_name' => $address->recipient_name,
                    'address_line' => $address->address_line,
                    'province' => $address->province,
                    'city' => $address->city,
                    'district' => $address->district,
                    'postal_code' => $address->postal_code,
                    'province_id' => $address->province_id,
                    'city_id' => $address->city_id,
                    'district_id' => $address->district_id,
                    'note' => $address->note,
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

    protected function shippingOptions(): array
    {
        return [
            'JNE - Reguler',
            'JNT - Ekspres',
            'SiCepat - Best',
            'TIKI - Reguler',
        ];
    }
}
