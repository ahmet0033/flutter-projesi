<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndUsersSeeder::class,
            StoreSettingsSeeder::class,
            CatalogSeeder::class,
            ShippingMethodSeeder::class,
            CouponSeeder::class,
            ReviewSeeder::class,
            ShieldPermissionSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoOrderSeeder::class);
        }
    }
}
