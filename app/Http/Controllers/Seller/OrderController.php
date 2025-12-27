<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $order->load([
            'user',
            'items.product',
            'address.provinceRegion',
            'address.cityRegion',
            'address.districtRegion',
            'paymentMethod',
            'media',
        ]);

        return Inertia::render('Seller/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'shipping_cost' => $order->shipping_cost,
                'grand_total' => $order->grand_total,
                'shipping_service' => $order->shipping_service,
                'shipping_awb' => $order->shipping_awb,
                'note' => $order->note,
                'created_at' => $order->created_at?->format('d M Y, H:i'),
                'ordered_at' => $order->ordered_at?->format('d M Y, H:i'),
                'customer' => [
                    'name' => $order->user?->name,
                    'email' => $order->user?->email,
                    'phone' => $order->user?->phone,
                ],
                'shipping_address' => $order->address ? [
                    'recipient_name' => $order->address->recipient_name,
                    'phone' => $order->address->phone,
                    'address' => $order->address->address_line,
                    'district' => $order->address->district,
                    'city' => $order->address->city,
                    'province' => $order->address->province,
                    'postal_code' => $order->address->postal_code,
                    'label' => $order->address->label,
                ] : null,
                'payment_method' => $order->paymentMethod ? [
                    'name' => $order->paymentMethod->name,
                    'code' => $order->paymentMethod->code,
                ] : null,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->name,
                    'product_image' => $item->product?->image_url,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->subtotal,
                ]),
                'invoice_url' => route('seller.orders.invoice', $order->id),
                'customer_whatsapp_link' => $order->getCustomerWhatsAppLink(),
                'payment_proof_url' => $order->payment_proof_url,
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

        $sellerDocument = $store->sellerDocument;
        if (! $sellerDocument || ! $sellerDocument->isApproved()) {
             return Redirect::back()->with('error', 'Verifikasi dokumen diperlukan untuk memproses pesanan.');
        }

        // Store previous status for notification
        $previousStatus = $order->status;
        $previousPaymentStatus = $order->payment_status;

        $rules = [
            'status' => ['required', 'string', Rule::in(array_column($this->orderStatuses(), 'value'))],
            'payment_status' => ['required', 'string', Rule::in(array_column($this->paymentStatuses(), 'value'))],
        ];

        // Require payment proof when changing payment_status to 'paid'
        if ($request->input('payment_status') === 'paid' && $order->payment_status !== 'paid') {
            $rules['payment_proof'] = ['required', 'image', 'max:5120']; // 5MB max
        } else {
            $rules['payment_proof'] = ['nullable', 'image', 'max:5120'];
        }

        $data = $request->validate($rules);

        // Handle payment proof upload
        \Log::info('Payment proof upload check', [
            'hasFile' => $request->hasFile('payment_proof'),
            'allFiles' => array_keys($request->allFiles()),
            'payment_status' => $data['payment_status'],
            'order_id' => $order->id,
        ]);

        if ($request->hasFile('payment_proof')) {
            \Log::info('Uploading payment proof', ['order_id' => $order->id]);
            $order->addMediaFromRequest('payment_proof')
                ->toMediaCollection('payment_proof');
            \Log::info('Payment proof uploaded successfully', ['order_id' => $order->id]);
        }

        $order->update([
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
        ]);

        // Send notification email if status changed
        $newStatus = $data['status'];
        $newPaymentStatus = $data['payment_status'];

        if ($previousStatus !== $newStatus || $previousPaymentStatus !== $newPaymentStatus) {
            $notificationService = app(\App\Services\OrderNotificationService::class);
            $notificationService->notifyStatusChanged(
                order: $order,
                previousStatus: $previousStatus,
                newStatus: $newStatus,
                previousPaymentStatus: $previousPaymentStatus,
                newPaymentStatus: $newPaymentStatus,
            );
        }

        return Redirect::back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function invoice(Request $request, Order $order)
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        abort_if($order->store_id !== $store->id, 403);

        $order->load([
            'items.product',
            'store',
            'paymentMethod',
            'address',
            'user',
        ]);

        $pdf = Pdf::loadView('invoices.invoice', [
            'order' => $order,
        ]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    private function getStoreOrRedirect(Request $request): Store|RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (! $store) {
            return Redirect::route('seller.settings.edit')->with('error', 'Lengkapi profil toko terlebih dahulu.');
        }

        return $store;
    }

    private function orderStatuses(): array
    {
        return [
            ['value' => 'pending_payment', 'label' => 'Menunggu Konfirmasi'],
            ['value' => 'processing', 'label' => 'Diproses'],
            ['value' => 'ready_for_pickup', 'label' => 'Siap Diambil'],
            ['value' => 'shipped', 'label' => 'Dikirim'],
            ['value' => 'delivered', 'label' => 'Diterima'],
            ['value' => 'completed', 'label' => 'Selesai'],
            ['value' => 'cancelled', 'label' => 'Dibatalkan'],
        ];
    }

    private function paymentStatuses(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Belum Dibayar'],
            ['value' => 'paid', 'label' => 'Lunas'],
        ];
    }
}
