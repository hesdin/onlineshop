<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $store = Store::where('user_id', $user->id)->first();

        if (!$store) {
            return Inertia::render('Seller/Reviews/Index', [
                'summary' => [
                    'average_rating' => 0,
                    'total_reviews' => 0,
                    'new_reviews_count' => 0,
                    'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
                ],
                'reviews' => ['data' => []],
                'filters' => [
                    'rating' => null,
                    'sort' => 'latest',
                ],
            ]);
        }

        $productIds = $store->products()->pluck('id');
        $ratingFilter = $request->integer('rating');
        $sort = $request->string('sort', 'latest')->toString();

        // Calculate summary stats
        $totalReviews = Review::whereIn('product_id', $productIds)->count();
        $averageRating = $totalReviews > 0
            ? round(Review::whereIn('product_id', $productIds)->avg('rating'), 1)
            : 0;

        // New reviews (last 7 days)
        $newReviewsCount = Review::whereIn('product_id', $productIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Get distribution
        $distribution = Review::whereIn('product_id', $productIds)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Fill missing ratings
        $fullDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $fullDistribution[$i] = $distribution[$i] ?? 0;
        }

        // Build reviews query
        $reviewsQuery = Review::whereIn('product_id', $productIds)
            ->with(['user:id,name', 'product:id,name,slug', 'order:id,order_number'])
            ->when($ratingFilter, fn ($q) => $q->where('rating', $ratingFilter));

        // Apply sorting
        switch ($sort) {
            case 'rating_high':
                $reviewsQuery->orderByDesc('rating')->orderByDesc('created_at');
                break;
            case 'rating_low':
                $reviewsQuery->orderBy('rating')->orderByDesc('created_at');
                break;
            default:
                $reviewsQuery->orderByDesc('created_at');
        }

        $reviews = $reviewsQuery
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toDateTimeString(),
                'created_at_human' => $review->created_at?->diffForHumans(),
                'user' => [
                    'name' => $review->user?->name ?? 'Pembeli',
                    'initial' => strtoupper(substr($review->user?->name ?? 'P', 0, 1)),
                ],
                'product' => [
                    'id' => $review->product?->id,
                    'name' => $review->product?->name,
                    'slug' => $review->product?->slug,
                ],
                'order' => [
                    'id' => $review->order?->id,
                    'order_number' => $review->order?->order_number,
                ],
            ]);

        return Inertia::render('Seller/Reviews/Index', [
            'summary' => [
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'new_reviews_count' => $newReviewsCount,
                'distribution' => $fullDistribution,
            ],
            'reviews' => $reviews,
            'filters' => [
                'rating' => $ratingFilter ?: null,
                'sort' => $sort,
            ],
        ]);
    }
}
