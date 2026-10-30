<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'order_id' => null,
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'is_verified_purchase' => false,
            'is_approved' => true,
            'approved_at' => now(),
        ];
    }
}
