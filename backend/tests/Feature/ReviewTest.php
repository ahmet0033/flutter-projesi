<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
});

test('public review list only returns approved reviews', function () {
    $product = Product::firstOrFail();
    $approvedUser = User::factory()->create();
    $pendingUser = User::factory()->create();

    Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $approvedUser->id,
        'rating' => 5,
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $pendingUser->id,
        'rating' => 3,
        'is_approved' => false,
        'approved_at' => null,
    ]);

    $this->getJson('/api/v1/products/'.$product->slug.'/reviews')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.rating', 5);
});

test('a customer can submit a review for moderation', function () {
    $product = Product::firstOrFail();
    $user = User::factory()->create();
    $user->assignRole('customer');
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/products/'.$product->slug.'/reviews', [
        'rating' => 5,
        'title' => 'Great',
        'body' => 'This product exceeded my expectations.',
    ])->assertCreated();

    $this->assertDatabaseHas('reviews', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'is_approved' => false,
    ]);

    $this->postJson('/api/v1/products/'.$product->slug.'/reviews', [
        'rating' => 4,
        'body' => 'Trying to review twice should fail.',
    ])->assertUnprocessable();
});
