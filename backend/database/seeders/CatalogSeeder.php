<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(
            file_get_contents(database_path('seeders/data/catalog.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        DB::transaction(function () use ($catalog) {
            $categories = collect($catalog['categories'])->mapWithKeys(function (array $category) {
                $model = Category::updateOrCreate(
                    ['slug' => $category['slug']],
                    [
                        'name' => $category['name'],
                        'description' => $category['description'] ?? null,
                        'is_active' => true,
                        'sort_order' => $category['sort_order'] ?? 0,
                    ],
                );

                return [$category['slug'] => $model];
            });

            $brands = collect($catalog['brands'])->mapWithKeys(function (array $brand) {
                $model = Brand::updateOrCreate(
                    ['slug' => $brand['slug']],
                    [
                        'name' => $brand['name'],
                        'description' => $brand['description'] ?? null,
                        'is_active' => true,
                    ],
                );

                return [$brand['slug'] => $model];
            });

            foreach ($catalog['products'] as $data) {
                $product = Product::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'category_id' => $categories[$data['category_slug']]->id,
                        'brand_id' => $brands[$data['brand_slug']]->id ?? null,
                        'name' => $data['name'],
                        'tagline' => $data['tagline'],
                        'description' => $data['description'],
                        'price_cents' => $data['price_cents'],
                        'compare_at_price_cents' => null,
                        'currency' => $data['currency'],
                        'image_url' => $data['image_url'],
                        'specs' => $data['specs'],
                        'is_active' => true,
                        'is_featured' => $data['is_featured'] ?? false,
                    ],
                );

                ProductVariant::updateOrCreate(
                    ['sku' => 'RE-'.Str::upper($data['slug'])],
                    [
                        'product_id' => $product->id,
                        'name' => 'Default',
                        'price_cents' => null,
                        'stock_quantity' => $data['stock'] ?? 0,
                        'is_active' => true,
                    ],
                );

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => 0],
                    [
                        'url' => $data['image_url'],
                        'alt' => $data['name'],
                    ],
                );
            }
        });
    }
}
