<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url' => 'https://picsum.photos/seed/'.fake()->unique()->numberBetween(1, 999999).'/1024/1024',
            'path' => null,
            'alt' => fake()->words(3, true),
            'sort_order' => 0,
        ];
    }
}
