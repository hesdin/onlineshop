<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get pending reviews (order items from completed orders without reviews)
        $pendingReviews = OrderItem::query()
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'delivered']);
            })
            ->whereDoesntHave('review')
            ->with(['product', 'order:id,order_number,created_at,store_id', 'order.store:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'order_id' => $item->order_id,
                    'product' => [
                        'id' => $item->product?->id,
                        'name' => $item->product?->name ?? $item->name,
                        'image_url' => $item->product?->image_url,
                    ],
                    'order' => [
                        'id' => $item->order?->id,
                        'order_number' => $item->order?->order_number,
                        'created_at' => $item->order?->created_at?->toDateTimeString(),
                        'store_name' => $item->order?->store?->name,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ];
            });

        // Get completed reviews
        $completedReviews = Review::query()
            ->where('user_id', $user->id)
            ->with(['product', 'order:id,order_number,created_at'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at?->toDateTimeString(),
                    'product' => [
                        'id' => $review->product?->id,
                        'name' => $review->product?->name,
                        'image_url' => $review->product?->image_url,
                    ],
                    'order' => [
                        'id' => $review->order?->id,
                        'order_number' => $review->order?->order_number,
                    ],
                ];
            });

        return Inertia::render('Customer/Reviews', [
            'pendingReviews' => $pendingReviews,
            'completedReviews' => $completedReviews,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Get the order item and verify ownership
        $orderItem = OrderItem::with('order')->findOrFail($validated['order_item_id']);

        if ($orderItem->order->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Check if review already exists
        $existingReview = Review::where('user_id', $user->id)
            ->where('order_item_id', $validated['order_item_id'])
            ->first();

        if ($existingReview) {
            return back()->withErrors(['order_item_id' => 'Anda sudah memberikan ulasan untuk produk ini.']);
        }

        // Create the review
        Review::create([
            'user_id' => $user->id,
            'product_id' => $orderItem->product_id,
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Ulasan berhasil dikirim!');
    }
}
