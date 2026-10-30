<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'standard',
                'name' => 'Standard delivery',
                'description' => '3-5 business days',
                'price_cents' => 1000,
                'free_over_cents' => 100000,
                'sort_order' => 1,
            ],
            [
                'code' => 'express',
                'name' => 'Express delivery',
                'description' => '1-2 business days',
                'price_cents' => 2500,
                'free_over_cents' => null,
                'sort_order' => 2,
            ],
            [
                'code' => 'pickup',
                'name' => 'Store pickup',
                'description' => 'Pick up from Mersin',
                'price_cents' => 0,
                'free_over_cents' => null,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::updateOrCreate(
                ['code' => $method['code']],
                $method + ['is_active' => true],
            );
        }
    }
}
