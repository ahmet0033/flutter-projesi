<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\QuoteRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
        private readonly CouponService $coupons,
    ) {}

    public function shippingMethods(): AnonymousResourceCollection
    {
        return ShippingMethodResource::collection(
            ShippingMethod::query()->active()->orderBy('sort_order')->get(),
        );
    }

    public function validateCoupon(QuoteRequest $request): JsonResponse
    {
        $cart = $this->carts->load($this->carts->resolve($request));
        $subtotal = $cart->subtotalCents();
        $coupon = $this->coupons->findValid(
            $request->validated()['coupon_code'] ?? null,
            $request->user(),
            $subtotal,
        );

        return response()->json([
            'coupon' => $coupon ? [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ] : null,
            'discount_cents' => $coupon ? $this->coupons->discountCents($coupon, $subtotal) : 0,
        ]);
    }

    public function quote(QuoteRequest $request): JsonResponse
    {
        $cart = $this->carts->load($this->carts->resolve($request));
        $data = $request->validated();

        $shipping = isset($data['shipping_method_id'])
            ? ShippingMethod::query()->active()->find($data['shipping_method_id'])
            : null;

        $coupon = $this->coupons->findValid(
            $data['coupon_code'] ?? null,
            $request->user(),
            $cart->subtotalCents(),
        );

        return response()->json(
            $this->checkout->quote($cart, $coupon, $shipping),
        );
    }
}
