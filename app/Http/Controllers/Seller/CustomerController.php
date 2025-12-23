<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $store = $this->getStoreOrRedirect($request);
        if (! ($store instanceof Store)) {
            return $store;
        }

        $search = $request->string('search')->toString();

        // Get unique customers who have ordered from this store
        $customers = Order::with('user:id,name,email,phone,created_at')
            ->where('store_id', $store->id)
            ->whereNotNull('user_id')
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(grand_total) as total_spent')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->groupBy('user_id')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('last_order_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($order) => [
                'id' => $order->user_id,
                'name' => $order->user?->name,
                'email' => $order->user?->email,
                'phone' => $order->user?->phone,
                'total_orders' => $order->total_orders,
                'total_spent' => $order->total_spent,
                'last_order_at' => $order->last_order_at,
                'member_since' => $order->user?->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Seller/Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    private function getStoreOrRedirect(Request $request): Store|RedirectResponse
    {
        $store = $request->user()?->store;

        if (! $store) {
            return redirect()->route('seller.settings.edit')
                ->with('error', 'Anda harus mengatur toko terlebih dahulu.');
        }

        return $store;
    }
}
