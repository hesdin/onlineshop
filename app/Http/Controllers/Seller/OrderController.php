<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $orders = Order::with('user:id,name,email')
            ->where('store_id', $store->id)
            ->when($search, fn ($query) => $query->where('order_number', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'grand_total' => $order->grand_total,
                'customer' => [
                    'name' => $order->user?->name,
                    'email' => $order->user?->email,
                ],
                'created_at' => $order->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Seller/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status ?: null,
            ],
            'statusOptions' => $this->orderStatuses(),
            'paymentStatusOptions' => $this->paymentStatuses(),
        ]);
    }

    public function show(Request $request, Order $order): Response|RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        abort_if($order->store_id !== $store->id, 403);

        $order->load(['user', 'items.product']);

        return Inertia::render('Seller/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'grand_total' => $order->grand_total,
                'shipping_cost' => $order->shipping_cost,
                'note' => $order->note,
                'created_at' => $order->created_at?->toDateTimeString(),
                'customer' => [
                    'name' => $order->user?->name,
                    'email' => $order->user?->email,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->subtotal,
                ]),
            ],
            'statusOptions' => $this->orderStatuses(),
            'paymentStatusOptions' => $this->paymentStatuses(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        abort_if($order->store_id !== $store->id, 403);

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(array_column($this->orderStatuses(), 'value'))],
            'payment_status' => ['required', 'string', Rule::in(array_column($this->paymentStatuses(), 'value'))],
        ]);

        $order->update($data);

        return Redirect::back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    private function getStoreOrRedirect(Request $request): Store|RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (! $store) {
            return Redirect::route('seller.store.edit')->with('error', 'Lengkapi profil toko terlebih dahulu.');
        }

        return $store;
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
