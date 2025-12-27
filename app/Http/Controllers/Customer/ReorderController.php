<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReorderController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get unique products from completed orders
        $reorderItems = OrderItem::query()
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'delivered']);
            })
            ->with(['product.store', 'order:id,created_at'])
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $latestItem = $items->sortByDesc('order.created_at')->first();
                $product = $latestItem->product;

                if (!$product) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'stock' => $product->stock,
                    'is_available' => $product->status !== 'inactive' && $product->stock > 0,
                    'store' => [
                        'id' => $product->store?->id,
                        'name' => $product->store?->name,
                        'slug' => $product->store?->slug,
                    ],
                    'last_purchased' => [
                        'quantity' => $latestItem->quantity,
                        'unit_price' => $latestItem->unit_price,
                        'date' => $latestItem->order?->created_at?->toDateTimeString(),
                    ],
                    'purchase_count' => $items->sum('quantity'),
                ];
            })
            ->filter()
            ->sortByDesc('last_purchased.date')
            ->values()
            ->take(50);

        return Inertia::render('Customer/Reorder', [
            'products' => $reorderItems,
        ]);
    }
}
