<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $reviews = $product->approvedReviews()
            ->with('user')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return ReviewResource::collection($reviews);
    }

    public function store(ReviewRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (Review::query()->where('user_id', $user->id)->where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages([
                'review' => 'You have already reviewed this product.',
            ]);
        }

        $order = $user->orders()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->latest()
            ->first();

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order?->id,
            'rating' => $request->validated()['rating'],
            'title' => $request->validated()['title'] ?? null,
            'body' => $request->validated()['body'],
            'is_verified_purchase' => $order !== null,
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => 'Review submitted for moderation.',
            'review' => new ReviewResource($review->load('user')),
        ], 201);
    }
}
