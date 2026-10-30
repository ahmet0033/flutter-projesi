<?php

use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
});

test('a guest can build a cart with a cart token', function () {
    $variant = ProductVariant::query()->firstOrFail();
    $variant->update(['stock_quantity' => 5]);
    $token = (string) Str::uuid();

    $response = $this->withHeader('X-Cart-Token', $token)
        ->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('data.item_count', 2)
        ->assertJsonPath('data.items.0.quantity', 2);

    $itemId = $response->json('data.items.0.id');

    $this->withHeader('X-Cart-Token', $token)
        ->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.item_count', 2);

    $this->withHeader('X-Cart-Token', $token)
        ->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 4])
        ->assertOk()
        ->assertJsonPath('data.item_count', 4);

    $this->withHeader('X-Cart-Token', $token)
        ->deleteJson("/api/v1/cart/items/{$itemId}")
        ->assertOk()
        ->assertJsonPath('data.item_count', 0);
});

test('a customer can merge a guest cart after login', function () {
    $variant = ProductVariant::query()->firstOrFail();
    $variant->update(['stock_quantity' => 5]);
    $token = (string) Str::uuid();

    $this->withHeader('X-Cart-Token', $token)
        ->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ])
        ->assertCreated();

    $user = User::factory()->create();
    $user->assignRole('customer');
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/cart/merge', ['guest_token' => $token])
        ->assertCreated()
        ->assertJsonPath('data.item_count', 3);
});
