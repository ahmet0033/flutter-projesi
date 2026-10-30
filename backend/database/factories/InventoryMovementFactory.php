<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'quantity_delta' => fake()->numberBetween(-10, 25),
            'reason' => fake()->randomElement(['manual', 'restock', 'adjustment']),
            'note' => fake()->sentence(),
        ];
    }
}
