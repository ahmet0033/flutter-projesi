<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(1000, 20000);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'product_name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'unit_price_cents' => $price,
            'quantity' => $quantity,
            'line_total_cents' => $price * $quantity,
            'specs' => [],
        ];
    }
}
