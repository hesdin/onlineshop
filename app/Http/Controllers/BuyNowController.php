<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyNowController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'shipping_method' => ['nullable', 'string', 'in:pickup,delivery'],
        ]);

        $product = Product::query()
            ->with([
                'store:id,name,is_umkm,tax_status,bumn_partner,province_id,city_id,district_id',
                'store.provinceRegion:id,name',
                'store.cityRegion:id,name',
            ])
            ->findOrFail($validated['product_id']);

        // Validate stock
        if ($product->stock !== null && $product->stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi.']);
        }

        // Validate min order
        $minOrder = $product->min_order ?: 1;
        if ($validated['quantity'] < $minOrder) {
            return back()->withErrors(['quantity' => "Minimal pembelian {$minOrder} pcs."]);
        }

        $price = $product->sale_price ?? $product->price ?? 0;
        $store = $product->store;
        $location = $this->formatLocation($product) ?: $this->formatStoreLocation($store);

        $groups = [
            [
                'storeId' => $store?->id,
                'vendor' => $store?->name ?? 'Toko',
                'location' => $location,
                'benefit' => $this->storeBenefit($store),
                'items' => [
                    [
                        'id' => 'buy-now-'.$product->id, // Temporary ID for buy now mode
                        'productId' => $product->id,
                        'name' => $product->name,
                        'note' => '',
                        'price' => (int) $price,
                        'qty' => (int) $validated['quantity'],
                        'subtotal' => (int) ($price * $validated['quantity']),
                        'img' => $product->image_url ?: null,
                        'tags' => $this->productTags($product),
                        'status' => $product->stock === null || $product->stock > 0 ? 'Tersedia' : 'Stok habis',
                        'location' => $location,
                        'url' => route('product.detail', ['slug' => $product->slug, 'product' => $product]),
                        'stock' => $product->stock,
                        'minOrder' => $minOrder,
                        'type' => $product->item_type === Product::ITEM_TYPE_SERVICE ? 'Jasa' : 'Barang',
                        'weight' => $product->weight ? (int) $product->weight : null,
                        'shipping_method' => $validated['shipping_method'] ?? null,
                        'shipping_pickup' => (bool) $product->shipping_pickup,
                        'shipping_delivery' => (bool) $product->shipping_delivery,
                    ],
                ],
            ],
        ];

        $addresses = $this->addresses($user->id);

        // Store buy-now data in session for payment processing
        session()->put('buy_now', [
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'shipping_method' => $validated['shipping_method'] ?? null,
            'price' => $price,
        ]);

        return Inertia::render('Checkout', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'groups' => $groups,
            'addresses' => $addresses,
            'shippingOptions' => $this->shippingOptions(),
            'noteLimit' => 100,
            'isBuyNow' => true, // Flag to indicate direct buy mode
        ]);
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
