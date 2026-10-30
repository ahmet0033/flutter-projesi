<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    public function run(): void
    {
        StoreSetting::query()->firstOrCreate([], StoreSetting::defaults());
    }
}
