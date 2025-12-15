<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class PaymentPageController extends Controller
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
                    ])->with([
                        'store:id,name,is_umkm,tax_status,bumn_partner,province_id,city_id,district_id',
                        'store.provinceRegion:id,name',
                        'store.cityRegion:id,name',
                    ]);
                },
            ])
            ->first();

        $orders = $cart ? $this->buildOrders($cart) : [];

        // Get default address
        $addresses = \App\Models\Address::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $defaultAddress = $addresses->firstWhere('is_default') ?? $addresses->first();

        return Inertia::render('Payment', [
            'appName' => config('app.name', 'TP-PKK Marketplace'),
            'paymentMethods' => $this->paymentMethods(),
            'orders' => $orders,
            'selectedItems' => $selectedItems->all(),
            'addressId' => $defaultAddress?->id,
        ]);
    }

    protected function buildOrders(Cart $cart): array
    {
        return $cart->items
            ->groupBy('store_id')
            ->values()
            ->map(function ($items, $index) {
                $product = $items->first()->product;
                $store = $product?->store;
                $total = $items->sum(function ($item) {
                    $product = $item->product;
                    $price = $item->unit_price ?? $product?->sale_price ?? $product?->price ?? 0;

                    return $price * $item->quantity;
                });

                $selected = $items->map(function ($item) {
                    $product = $item->product;
                    $weight = $product?->weight ? (int) $product->weight : 0;
                    $type = $product?->item_type === Product::ITEM_TYPE_SERVICE ? 'Jasa' : 'Barang';

                    return [
                        'qty' => (int) $item->quantity,
                        'weight' => $weight,
                        'type' => $type,
                        'price' => (int) ($item->unit_price ?? $product?->sale_price ?? $product?->price ?? 0),
                    ];
                });

                $itemCount = $selected->sum('qty');
                $totalWeight = $selected->sum(fn ($item) => ($item['weight'] ?? 0) * $item['qty']);
                $hasOnlyServices = $selected->every(fn ($item) => $item['type'] === 'Jasa');
                $typeLabel = $hasOnlyServices
                    ? 'Total Harga Jasa'
                    : "Total Harga {$itemCount} Barang".($totalWeight ? ' ('.number_format($totalWeight / 1000, 2).' kg)' : '');

                // Build items list for WhatsApp
                $itemsList = $items->map(function ($item) {
                    $product = $item->product;
                    $price = $item->unit_price ?? $product?->sale_price ?? $product?->price ?? 0;
                    $subtotal = $price * $item->quantity;
                    return [
                        'name' => $product?->name,
                        'quantity' => $item->quantity,
                        'subtotal' => $subtotal,
                    ];
                })->values()->all();

                // Format phone for WhatsApp
                $phone = $store?->phone;
                $whatsappLink = null;
                if ($phone) {
                    $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
                    if (!str_starts_with($formattedPhone, '62')) {
                        $formattedPhone = '62' . ltrim($formattedPhone, '0');
                    }

                    // Pre-checkout message
                    $itemsText = collect($itemsList)->map(function ($item) {
                        $subtotal = number_format($item['subtotal'], 0, ',', '.');
                        return "{$item['name']} x{$item['quantity']}";
                    })->join("\n");

                    $totalFormatted = number_format($total, 0, ',', '.');
                    $message = urlencode("Halo {$store->name},\n\nSaya tertarik dengan produk:\n{$itemsText}\n\nTotal: Rp {$totalFormatted}\n\nUntuk metode pembayaran Manual Transfer, mohon info rekening bank untuk transfer.\n\nTerima kasih!");
                    $whatsappLink = "https://wa.me/{$formattedPhone}?text={$message}";
                }

                return [
                    'title' => 'Pesanan '.($index + 1),
                    'vendor' => $store?->name ?? 'Toko',
                    'store_phone' => $store?->phone,
                    'store_rating' => $store?->rating,
                    'store_response_time' => $store?->response_time_label,
                    'typeLabel' => $typeLabel,
                    'benefit' => $this->storeBenefit($store),
                    'shipping' => 0,
                    'total' => (int) $total,
                    'items' => $itemsList,
                    'whatsapp_link' => $whatsappLink,
                ];
            })
            ->all();
    }

    protected function paymentMethods(): array
    {
        $methods = \App\Models\PaymentMethod::query()
            ->where('is_active', true)
            ->get();

        // Group by channel
        $grouped = $methods->groupBy('channel')->map(function ($items, $channel) {
            $labels = [
                'cod' => 'Cash on Delivery',
                'manual_transfer' => 'Transfer Manual',
            ];

            $notes = [
                'cod' => 'Bayar saat barang diterima',
                'manual_transfer' => 'Transfer ke rekening toko',
            ];

            return [
                'label' => $labels[$channel] ?? ucfirst(str_replace('_', ' ', $channel)),
                'note' => $notes[$channel] ?? '',
                'methods' => $items->map(function ($method) {
                    return [
                        'id' => $method->code,
                        'name' => $method->name,
                        'description' => $method->metadata['description'] ?? '',
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return $grouped;
    }

    protected function storeBenefit($store): ?string
    {
        if (! $store) {
            return null;
        }

        if ($store->bumn_partner) {
            return 'Gratis Ongkir';
        }

        if ($store->is_umkm) {
            return 'UMKM';
        }

        return $store->tax_status ?: null;
    }
}
