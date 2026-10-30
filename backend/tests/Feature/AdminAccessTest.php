<?php

use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
});

test('admin can access the Filament panel', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('customer cannot access the Filament panel', function () {
    $customer = User::where('email', 'customer@redeast.agency')->firstOrFail();

    $this->actingAs($customer)->get('/admin')->assertForbidden();
});

test('api health endpoint responds', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'service' => 'redeast-api',
            'version' => 'v1',
        ]);
});
