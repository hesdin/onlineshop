<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $orders = Order::query()
            ->with(['user', 'store'])
            ->when($search, fn ($query) => $query->where('order_number', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'store_name' => $order->store?->name,
                'total_amount' => $order->grand_total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status ?: null,
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['user', 'store', 'items.product']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total_amount' => $order->grand_total,
                'shipping_cost' => $order->shipping_cost,
                'notes' => $order->note,
                'created_at' => $order->created_at->toDateTimeString(),
                'customer' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'store' => $order->store ? [
                    'name' => $order->store->name,
                ] : null,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->subtotal,
                ]),
            ],
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,completed,cancelled',
            'payment_status' => 'required|string|in:unpaid,paid,failed,refunded',
        ]);

        $order->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ]);

        return Redirect::back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
