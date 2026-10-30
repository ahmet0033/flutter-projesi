<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->active()->limit(4)->get();
        $users = User::query()->whereIn('email', ['admin@redeast.agency', 'customer@redeast.agency'])->get();

        if ($products->isEmpty() || $users->isEmpty()) {
            return;
        }

        $samples = [
            'Excellent build quality and the delivery was fast.',
            'Exactly as described. Would order from RedEast again.',
            'Great value for the price; the packaging was excellent.',
            'Very happy with this purchase and the support team.',
        ];

        foreach ($products as $productIndex => $product) {
            foreach ($users as $userIndex => $user) {
                Review::updateOrCreate(
                    ['user_id' => $user->id, 'product_id' => $product->id],
                    [
                        'rating' => $userIndex === 0 ? 5 : 4,
                        'title' => 'Verified from a real order',
                        'body' => $samples[($productIndex + $userIndex) % count($samples)],
                        'is_verified_purchase' => true,
                        'is_approved' => true,
                        'approved_at' => now(),
                    ],
                );
            }
        }
    }
}
