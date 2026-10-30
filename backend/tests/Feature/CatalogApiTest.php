<?php

use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CatalogSeeder::class);
});

test('catalog lists active products with pagination', function () {
    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonPath('meta.total', 20)
        ->assertJsonCount(12, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'price', 'currency', 'image', 'specs', 'category', 'brand'],
            ],
            'meta' => ['current_page', 'total', 'per_page'],
        ]);
});

test('catalog can filter by category and search', function () {
    $this->getJson('/api/v1/products?category=phones&per_page=50')
        ->assertOk()
        ->assertJsonPath('meta.total', 4);

    $this->getJson('/api/v1/products?search=nova&per_page=50')
        ->assertOk()
        ->assertJsonPath('meta.total', 4);
});

test('catalog product detail is available by slug', function () {
    $this->getJson('/api/v1/products/nova-x')
        ->assertOk()
        ->assertJsonPath('data.name', 'RedEast Nova X')
        ->assertJsonPath('data.price', '$899.00')
        ->assertJsonPath('data.category.slug', 'phones')
        ->assertJsonCount(1, 'data.variants');
});

test('inactive product detail returns not found', function () {
    Product::where('slug', 'nova-x')->update(['is_active' => false]);

    $this->getJson('/api/v1/products/nova-x')->assertNotFound();
});

test('categories and brands endpoints return active records', function () {
    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(7, 'data');

    $this->getJson('/api/v1/brands')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'red-east');
});
