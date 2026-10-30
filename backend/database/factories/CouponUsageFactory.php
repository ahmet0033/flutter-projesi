<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CouponUsage>
 */
class CouponUsageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'user_id' => User::factory(),
            'order_id' => null,
            'used_at' => now(),
        ];
    }
}
