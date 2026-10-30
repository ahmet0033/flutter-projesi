<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Database\Seeders\ShippingMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ShippingMethodSeeder::class);
});

test('admin order, coupon, shipping, and customer pages render', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $order = Order::factory()->create(['user_id' => $customer->id]);
    $product = Product::firstOrFail();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
    Payment::factory()->create([
        'order_id' => $order->id,
        'amount_cents' => $order->total_cents,
    ]);

    $this->actingAs($admin);

    $this->get('/admin/orders')->assertOk();
    $this->get('/admin/orders/'.$order->number)->assertOk();
    $this->get('/admin/coupons')->assertOk();
    $this->get('/admin/shipping-methods')->assertOk();
    $this->get('/admin/reviews')->assertOk();
    $this->get('/admin/users')->assertOk();
});
