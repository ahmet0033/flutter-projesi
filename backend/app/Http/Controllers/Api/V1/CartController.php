<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CartItemRequest;
use App\Http\Requests\Api\V1\CartItemUpdateRequest;
use App\Http\Requests\Api\V1\MergeCartRequest;
use App\Http\Resources\CartResource;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function show(Request $request): CartResource
    {
        return new CartResource($this->carts->load($this->carts->resolve($request)));
    }

    public function add(CartItemRequest $request): CartResource
    {
        $cart = $this->carts->resolve($request);
        $variant = ProductVariant::query()->with('product')->findOrFail(
            $request->validated()['product_variant_id'],
        );

        $cart = $this->carts->add(
            $cart,
            $variant,
            (int) ($request->validated()['quantity'] ?? 1),
        );

        return new CartResource($cart);
    }

    public function update(CartItemUpdateRequest $request, int $item): CartResource
    {
        $cart = $this->carts->resolve($request);
        $cartItem = $cart->items()->findOrFail($item);

        return new CartResource(
            $this->carts->update($cart, $cartItem, $request->validated()['quantity']),
        );
    }

    public function destroy(Request $request, int $item): CartResource
    {
        $cart = $this->carts->resolve($request);
        $cartItem = $cart->items()->findOrFail($item);

        return new CartResource($this->carts->remove($cart, $cartItem));
    }

    public function merge(MergeCartRequest $request): CartResource
    {
        $token = $request->validated()['guest_token'] ?? $request->header('X-Cart-Token');

        return new CartResource(
            $this->carts->merge($request->user(), $token),
        );
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->carts->resolve($request);
        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared.']);
    }
}
