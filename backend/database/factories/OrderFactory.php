<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(5000, 50000);

        return [
            'number' => 'RE-'.strtoupper(fake()->unique()->bothify('??####')),
            'user_id' => User::factory(),
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_method' => 'cod',
            'currency' => 'USD',
            'subtotal_cents' => $subtotal,
            'discount_cents' => 0,
            'shipping_cents' => 1000,
            'tax_cents' => 0,
            'total_cents' => $subtotal + 1000,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'shipping_address' => ['city' => fake()->city(), 'country' => 'TR'],
            'placed_at' => now(),
        ];
    }
}
