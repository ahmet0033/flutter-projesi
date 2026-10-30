<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->numberBetween(1999, 249999);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'tagline' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'price_cents' => $price,
            'compare_at_price_cents' => fake()->boolean(30) ? $price + 5000 : null,
            'currency' => 'USD',
            'image_path' => null,
            'image_url' => 'https://picsum.photos/seed/'.Str::slug($name).'/1024/1024',
            'specs' => [
                'material' => fake()->word(),
                'warranty' => fake()->randomElement(['1 year', '2 years']),
            ],
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
        ];
    }
}
