<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function findValid(?string $code, ?User $user, int $subtotalCents): ?Coupon
    {
        if (! $code) {
            return null;
        }

        $coupon = Coupon::query()
            ->where('code', Str::upper(trim($code)))
            ->first();

        if (! $coupon || ! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code.']);
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon is not active yet.']);
        }

        if ($coupon->ends_at && now()->gt($coupon->ends_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has reached its usage limit.']);
        }

        if ($user && $coupon->per_user_limit !== null
            && $coupon->usages()->where('user_id', $user->id)->count() >= $coupon->per_user_limit) {
            throw ValidationException::withMessages(['coupon_code' => 'You have already used this coupon.']);
        }

        if ($coupon->min_subtotal_cents !== null && $subtotalCents < $coupon->min_subtotal_cents) {
            throw ValidationException::withMessages(['coupon_code' => 'The cart does not meet this coupon minimum.']);
        }

        return $coupon;
    }

    public function discountCents(Coupon $coupon, int $subtotalCents): int
    {
        $discount = $coupon->type === 'percentage'
            ? (int) round($subtotalCents * $coupon->value / 100)
            : $coupon->value;

        if ($coupon->max_discount_cents !== null) {
            $discount = min($discount, $coupon->max_discount_cents);
        }

        return max(0, min($discount, $subtotalCents));
    }

    public function recordUsage(Coupon $coupon, ?User $user, Order $order): void
    {
        $coupon->usages()->create([
            'user_id' => $user?->id,
            'order_id' => $order->id,
            'used_at' => now(),
        ]);

        $coupon->increment('used_count');
    }
}
