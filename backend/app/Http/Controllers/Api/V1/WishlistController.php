<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $request->user()->wishlists()
            ->with(['product.category', 'product.brand', 'product.images', 'product.variants'])
            ->latest()
            ->get()
            ->pluck('product')
            ->filter();

        return ProductResource::collection($products);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlists()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlists()
            ->where('product_id', $product->id)
            ->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
