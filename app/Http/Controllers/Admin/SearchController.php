<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('q')->toString();

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search Users
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'email']);

        if ($users->isNotEmpty()) {
            $results['users'] = [
                'label' => 'Pengguna',
                'items' => $users->map(fn ($user) => [
                    'id' => $user->id,
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'badge' => ucfirst($user->getRoleNames()->first() ?? 'User'),
                    'url' => "/admin/users/{$user->id}/edit",
                ]),
            ];
        }

        // Search Stores
        $stores = Store::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        if ($stores->isNotEmpty()) {
            $results['stores'] = [
                'label' => 'Toko',
                'items' => $stores->map(fn ($store) => [
                    'id' => $store->id,
                    'title' => $store->name,
                    'subtitle' => $store->slug,
                    'url' => "/admin/stores/{$store->id}/edit",
                ]),
            ];
        }

        // Search Products
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'slug', 'price']);

        if ($products->isNotEmpty()) {
            $results['products'] = [
                'label' => 'Produk',
                'items' => $products->map(fn ($product) => [
                    'id' => $product->id,
                    'title' => $product->name,
                    'subtitle' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                    'url' => "/admin/products/{$product->id}/edit",
                ]),
            ];
        }

        // Search Orders
        $orders = Order::where('order_number', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'order_number', 'status', 'grand_total']);

        if ($orders->isNotEmpty()) {
            $results['orders'] = [
                'label' => 'Pesanan',
                'items' => $orders->map(fn ($order) => [
                    'id' => $order->id,
                    'title' => $order->order_number,
                    'subtitle' => 'Rp ' . number_format($order->grand_total, 0, ',', '.'),
                    'badge' => ucfirst($order->status),
                    'url' => "/admin/orders/{$order->id}",
                ]),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
