<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with([
                'store.cityRegion',
                'items.product:id',
                'address.provinceRegion',
                'address.cityRegion',
                'paymentMethod:id,name'
            ])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(function (Order $order) {
                // Get first item and its product image
                $firstItem = $order->items->first();
                $product = $firstItem?->product;

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'grand_total' => $order->grand_total,
                    'store' => [
                        'id' => $order->store?->id,
                        'name' => $order->store?->name,
                        'city' => $order->store?->city,
                        'phone' => $order->store?->phone,
                        'bank_details' => [
                            'bank_name' => $order->store?->bank_name,
                            'account_number' => $order->store?->bank_account_number,
                            'account_name' => $order->store?->bank_account_name,
                        ],
                    ],
                    'store_image' => $order->store?->image_url,
                    'items_count' => $order->items_count,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'name' => $item->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                            'note' => $item->note,
                            'image_url' => $item->product?->image_url,
                        ];
                    }),
                    'first_item' => $firstItem ? [
                        'name' => $firstItem->name,
                        'quantity' => $firstItem->quantity,
                        'image_url' => $product?->image_url,
                    ] : null,
                    'shipping_info' => [
                        'courier' => $order->shipping_service, // You might want to format this
                        'service' => $order->shipping_service,
                        'tracking_number' => $order->shipping_awb,
                        'cost' => $order->shipping_cost,
                        'eta' => $order->shipping_eta,
                    ],
                    'shipping_address' => $order->address ? [
                        'recipient_name' => $order->address->recipient_name,
                        'phone' => $order->address->phone,
                        'address_line' => $order->address->address_line,
                        'city' => $order->address->city,
                        'province' => $order->address->province,
                        'postal_code' => $order->address->postal_code,
                        'full_address' => "{$order->address->address_line}, {$order->address->city}, {$order->address->province} {$order->address->postal_code}",
                    ] : null,
                    'payment_info' => [
                        'method' => $order->paymentMethod?->name,
                        'status' => $order->payment_status,
                    ],
                    'created_at' => $order->created_at?->toDateTimeString(),
                    'invoice_url' => route('orders.invoice.download', $order),
                ];
            })
            ->values();

        return Inertia::render('Customer/Transactions', [
            'orders' => $orders,
            'statusOptions' => $this->orderStatuses(),
            'paymentStatusOptions' => $this->paymentStatuses(),
        ]);
    }

    private function orderStatuses(): array
    {
        return [
            ['value' => 'pending_payment', 'label' => 'Menunggu Pembayaran'],
            ['value' => 'processing', 'label' => 'Diproses'],
            ['value' => 'shipped', 'label' => 'Dikirim'],
            ['value' => 'delivered', 'label' => 'Diterima'],
            ['value' => 'completed', 'label' => 'Selesai'],
            ['value' => 'cancelled', 'label' => 'Dibatalkan'],
        ];
    }

    private function paymentStatuses(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Menunggu'],
            ['value' => 'paid', 'label' => 'Dibayar'],
            ['value' => 'expired', 'label' => 'Kedaluwarsa'],
            ['value' => 'failed', 'label' => 'Gagal'],
        ];
    }
}
