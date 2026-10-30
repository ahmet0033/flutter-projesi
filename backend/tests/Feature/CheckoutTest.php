<?php

use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\CouponSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Database\Seeders\ShippingMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ShippingMethodSeeder::class);
    $this->seed(CouponSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('customer');
    Sanctum::actingAs($this->user);

    $this->variant = ProductVariant::query()->firstOrFail();
    $this->variant->update(['stock_quantity' => 5]);

    $this->postJson('/api/v1/cart/items', [
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ])->assertCreated();
});

test('checkout quote applies coupon and shipping', function () {
    $shipping = ShippingMethod::where('code', 'standard')->firstOrFail();

    $this->postJson('/api/v1/checkout/quote', [
        'shipping_method_id' => $shipping->id,
        'coupon_code' => 'WELCOME10',
    ])
        ->assertOk()
        ->assertJsonPath('coupon.code', 'WELCOME10')
        ->assertJsonPath('shipping_method.code', 'standard')
        ->assertJsonStructure([
            'subtotal_cents', 'discount_cents', 'shipping_cents', 'tax_cents', 'total_cents',
        ]);
});

test('a customer can place an order and stock is decremented', function () {
    $shipping = ShippingMethod::where('code', 'standard')->firstOrFail();
    $stockBefore = $this->variant->stock_quantity;

    $response = $this->postJson('/api/v1/orders', [
        'shipping_method_id' => $shipping->id,
        'coupon_code' => 'WELCOME10',
        'payment_method' => 'cod',
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'customer_phone' => '+905551112233',
        'shipping_address' => [
            'name' => 'Test Customer',
            'phone' => '+905551112233',
            'line1' => 'Test Street 1',
            'city' => 'Mersin',
            'postal_code' => '33000',
            'country' => 'TR',
        ],
        'idempotency_key' => 'test-order-1',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.payment_status', 'pending');

    expect($this->variant->fresh()->stock_quantity)->toBe($stockBefore - 1);
    expect($this->user->orders()->count())->toBe(1);

    $this->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.item_count', 0);
});

test('order creation is idempotent', function () {
    $shipping = ShippingMethod::where('code', 'standard')->firstOrFail();

    $payload = [
        'shipping_method_id' => $shipping->id,
        'payment_method' => 'cod',
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'shipping_address' => [
            'name' => 'Test Customer',
            'phone' => '+905551112233',
            'line1' => 'Test Street 1',
            'city' => 'Mersin',
            'postal_code' => '33000',
            'country' => 'TR',
        ],
        'idempotency_key' => 'same-key',
    ];

    $first = $this->postJson('/api/v1/orders', $payload)->assertCreated();
    $second = $this->postJson('/api/v1/orders', $payload)->assertCreated();

    expect($second->json('data.number'))->toBe($first->json('data.number'));
    expect($this->user->orders()->count())->toBe(1);
});

test('insufficient stock rejects the order', function () {
    $this->variant->update(['stock_quantity' => 0]);

    $shipping = ShippingMethod::where('code', 'standard')->firstOrFail();

    $this->postJson('/api/v1/orders', [
        'shipping_method_id' => $shipping->id,
        'payment_method' => 'cod',
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'shipping_address' => [
            'name' => 'Test Customer',
            'phone' => '+905551112233',
            'line1' => 'Test Street 1',
            'city' => 'Mersin',
            'postal_code' => '33000',
            'country' => 'TR',
        ],
    ])->assertUnprocessable();
});
