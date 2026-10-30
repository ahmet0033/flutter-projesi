<?php

use App\Filament\Pages\Reports\CustomerReport;
use App\Filament\Pages\Reports\InventoryReport;
use App\Filament\Pages\Reports\ProductReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DemoOrderSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Database\Seeders\ShippingMethodSeeder;
use Database\Seeders\StoreSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(StoreSettingsSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ShippingMethodSeeder::class);
});

test('seeded demo orders make dashboard reports visible', function () {
    $this->seed(DemoOrderSeeder::class);
    $admin = User::query()->where('email', 'admin@redeast.agency')->firstOrFail();

    $this->actingAs($admin);

    $this->get('/admin')->assertOk()->assertSee('RE-DEMO-002');
    $this->get(SalesReport::getUrl())->assertOk()->assertSee('RE-DEMO-001');
    $this->get(ProductReport::getUrl())->assertOk()->assertSee('RedEast Nova X');
    $this->get(CustomerReport::getUrl())->assertOk()->assertSee('RedEast Customer');
    $this->get(InventoryReport::getUrl())->assertOk()->assertSee('RE-NOVA-X');
});

test('demo orders populate the dashboard and seed idempotently', function () {
    $this->seed(DemoOrderSeeder::class);

    expect(Order::query()->count())->toBe(6)
        ->and(Order::query()->where('payment_status', Order::PAYMENT_PAID)->count())->toBe(4)
        ->and(Order::query()->where('payment_status', Order::PAYMENT_REFUNDED)->count())->toBe(1)
        ->and(OrderItem::query()->count())->toBe(7)
        ->and((int) OrderItem::query()->sum('quantity'))->toBe(11)
        ->and(Payment::query()->count())->toBe(6)
        ->and(ActivityLog::query()->where('description', 'like', 'Order RE-DEMO%')->count())->toBe(3)
        ->and(ProductVariant::query()->where('sku', 'RE-NOVA-MINI')->value('stock_quantity'))->toBe(4);

    $this->seed(DemoOrderSeeder::class);

    expect(Order::query()->count())->toBe(6)
        ->and(OrderItem::query()->count())->toBe(7)
        ->and((int) OrderItem::query()->sum('quantity'))->toBe(11)
        ->and(Payment::query()->count())->toBe(6);
});
