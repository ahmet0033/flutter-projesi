<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'manual',
            'method' => 'cod',
            'status' => 'pending',
            'amount_cents' => fake()->numberBetween(5000, 50000),
        ];
    }
}
