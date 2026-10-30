<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Work']),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'line1' => fake()->streetAddress(),
            'line2' => null,
            'city' => fake()->city(),
            'state' => null,
            'postal_code' => fake()->postcode(),
            'country' => 'TR',
            'is_default_shipping' => true,
            'is_default_billing' => true,
        ];
    }
}
