<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $store = Store::where('user_id', $request->user()->id)->first();

        if (! $store) {
            return Redirect::route('seller.settings.edit')->with('error', 'Lengkapi profil toko terlebih dahulu.');
        }

        $query = $request->string('q')->toString();

        $products = collect();
        $orders = collect();

        if ($query) {
            // Search products
            $products = Product::where('store_id', $store->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->with('category:id,name')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'status' => $product->status,
                    'category' => $product->category ? $product->category->name : null,
                    'image_url' => $product->image_url,
                ]);

            // Search orders
            $orders = Order::where('store_id', $store->id)
                ->where(function ($q) use ($query) {
                    $q->where('order_number', 'like', "%{$query}%")
                        ->orWhereHas('user', function ($userQuery) use ($query) {
                            $userQuery->where('name', 'like', "%{$query}%");
                        });
                })
                ->with('user:id,name')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user ? $order->user->name : 'Unknown',
                    'total' => $order->grand_total,
                    'status' => $order->status,
                    'created_at' => $order->created_at?->format('d M Y H:i'),
                ]);
        }

        return Inertia::render('Seller/Search/Index', [
            'query' => $query,
            'products' => $products,
            'orders' => $orders,
        ]);
    }
}
