<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
});

test('admin catalog pages render', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $product = Product::firstOrFail();

    $this->actingAs($admin);

    $this->get('/admin/products')->assertOk();
    $this->get('/admin/products/create')->assertOk();
    $this->get("/admin/products/{$product->slug}/edit")->assertOk();
    $this->get('/admin/categories')->assertOk();
    $this->get('/admin/brands')->assertOk();
});
