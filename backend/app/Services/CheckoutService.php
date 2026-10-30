<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\ShippingMethod;
use App\Support\StoreSettings;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CouponService $coupons,
        private readonly StoreSettings $settings,
    ) {}

    public function quote(Cart $cart, ?Coupon $coupon = null, ?ShippingMethod $shipping = null): array
    {
        $cart->loadMissing('items.variant.product');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
                throw ValidationException::withMessages(['cart' => 'A product in your cart is no longer available.']);
            }

            if ($item->quantity > $variant->stock_quantity) {
                throw ValidationException::withMessages([
                    'cart' => "Only {$variant->stock_quantity} left for {$variant->product->name}.",
                ]);
            }
        }

        $subtotal = (int) $cart->items->sum(fn ($item) => $item->lineTotalCents());
        $discount = $coupon ? $this->coupons->discountCents($coupon, $subtotal) : 0;
        $taxable = max(0, $subtotal - $discount);
        $shippingCost = $shipping ? $shipping->priceFor($taxable) : 0;

        $rate = $this->settings->taxRate();
        $inclusive = $this->settings->taxInclusive();
        $tax = $rate > 0
            ? (int) round($inclusive ? $taxable * $rate / (1 + $rate) : $taxable * $rate)
            : 0;

        $total = $taxable + $shippingCost + ($inclusive ? 0 : $tax);

        return [
            'currency' => $this->settings->currency(),
            'item_count' => $cart->itemCount(),
            'subtotal_cents' => $subtotal,
            'discount_cents' => $discount,
            'shipping_cents' => $shippingCost,
            'tax_cents' => $tax,
            'total_cents' => $total,
            'tax_rate' => $rate,
            'tax_inclusive' => $inclusive,
            'coupon' => $coupon ? [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
            ] : null,
            'shipping_method' => $shipping ? [
                'id' => $shipping->id,
                'code' => $shipping->code,
                'name' => $shipping->name,
            ] : null,
        ];
    }
}
