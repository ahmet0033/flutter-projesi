<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('customer');
    Sanctum::actingAs($this->user);
});

test('customers can manage a wishlist', function () {
    $product = Product::firstOrFail();

    $this->getJson('/api/v1/wishlist')->assertOk()->assertJsonCount(0, 'data');

    $this->postJson('/api/v1/wishlist/'.$product->slug)->assertCreated();

    $this->getJson('/api/v1/wishlist')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $product->slug);

    $this->deleteJson('/api/v1/wishlist/'.$product->slug)->assertOk();

    $this->getJson('/api/v1/wishlist')->assertOk()->assertJsonCount(0, 'data');
});
