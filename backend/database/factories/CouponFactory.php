<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE####')),
            'description' => fake()->sentence(),
            'type' => 'percentage',
            'value' => 10,
            'min_subtotal_cents' => null,
            'max_discount_cents' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'per_user_limit' => null,
            'is_active' => true,
        ];
    }
}
