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
            ->with(['store:id,name'])
            ->withCount('items')
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'grand_total' => $order->grand_total,
                    'store' => $order->store?->name,
                    'items_count' => $order->items_count,
                    'created_at' => $order->created_at?->toDateTimeString(),
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
