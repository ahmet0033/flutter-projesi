<?php

use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Database\Seeders\ShippingMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('placing an order creates a notification that can be read', function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ShippingMethodSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('customer');
    Sanctum::actingAs($user);

    $variant = ProductVariant::firstOrFail();
    $variant->update(['stock_quantity' => 5]);
    $shipping = ShippingMethod::where('code', 'standard')->firstOrFail();

    $this->postJson('/api/v1/cart/items', [
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ])->assertCreated();

    $this->postJson('/api/v1/orders', [
        'shipping_method_id' => $shipping->id,
        'payment_method' => 'cod',
        'customer_name' => 'Notification Customer',
        'customer_email' => $user->email,
        'shipping_address' => [
            'name' => 'Notification Customer',
            'phone' => '+905551112233',
            'line1' => 'Test Street 1',
            'city' => 'Mersin',
            'postal_code' => '33000',
            'country' => 'TR',
        ],
    ])->assertCreated();

    expect($user->notifications()->count())->toBe(1);

    $response = $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.data.title', 'Siparişiniz alındı');

    $notificationId = $response->json('data.0.id');

    $this->postJson('/api/v1/notifications/'.$notificationId.'/read')
        ->assertOk();

    expect($user->notifications()->first()->read_at)->not->toBeNull();

    $this->postJson('/api/v1/notifications/read-all')->assertOk();
});
