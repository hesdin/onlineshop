<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * Get user's reviews
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['product', 'order'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return ReviewResource::collection($reviews);
    }

    /**
     * Create review
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_item_id' => ['required', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Get order item and verify ownership
        $orderItem = OrderItem::with(['order', 'product'])
            ->findOrFail($validated['order_item_id']);

        // Verify the order belongs to user
        if ($orderItem->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Item pesanan tidak ditemukan.',
            ], 404);
        }

        // Check if order is completed/delivered
        if (!in_array($orderItem->order->status, ['completed', 'delivered'])) {
            return response()->json([
                'message' => 'Anda hanya dapat memberikan ulasan untuk pesanan yang sudah selesai.',
            ], 422);
        }

        // Check if already reviewed
        $existingReview = Review::where('order_item_id', $orderItem->id)->first();
        if ($existingReview) {
            return response()->json([
                'message' => 'Anda sudah memberikan ulasan untuk produk ini.',
            ], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $orderItem->product_id,
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $review->load(['user', 'product']);

        return response()->json([
            'message' => 'Ulasan berhasil ditambahkan.',
            'review' => new ReviewResource($review),
        ], 201);
    }
}
