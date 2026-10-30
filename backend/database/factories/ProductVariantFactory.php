<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => 'Default',
            'sku' => 'RE-'.strtoupper(fake()->unique()->bothify('??####')),
            'price_cents' => null,
            'stock_quantity' => fake()->numberBetween(0, 50),
            'options' => null,
            'is_active' => true,
        ];
    }
}
