<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\StoreSetting;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Support\StoreSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StoreSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->hasRole('super_admin') ? true : null;
        });

        foreach ([
            Brand::class,
            Category::class,
            Coupon::class,
            Order::class,
            Payment::class,
            Product::class,
            ProductVariant::class,
            Review::class,
            ShippingMethod::class,
            StoreSetting::class,
            User::class,
        ] as $model) {
            $model::observe(ActivityObserver::class);
        }
    }
}
