<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'description' => '10% off your first order',
                'type' => 'percentage',
                'value' => 10,
                'min_subtotal_cents' => 5000,
                'max_discount_cents' => 5000,
                'usage_limit' => 1000,
                'per_user_limit' => 1,
                'is_active' => true,
            ],
        );

        Coupon::updateOrCreate(
            ['code' => 'SAVE20'],
            [
                'description' => '$20 off orders over $200',
                'type' => 'fixed',
                'value' => 2000,
                'min_subtotal_cents' => 20000,
                'max_discount_cents' => null,
                'usage_limit' => 500,
                'per_user_limit' => 1,
                'is_active' => true,
            ],
        );
    }
}
