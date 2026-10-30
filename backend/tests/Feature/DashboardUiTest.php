<?php

use App\Filament\Pages\ManageStoreSettings;
use App\Filament\Pages\Reports\CustomerReport;
use App\Filament\Pages\Reports\InventoryReport;
use App\Filament\Pages\Reports\ProductReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\StaffResource;
use App\Models\ActivityLog;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\CheckoutService;
use App\Support\StoreSettings;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndUsersSeeder;
use Database\Seeders\ShieldPermissionSeeder;
use Database\Seeders\ShippingMethodSeeder;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndUsersSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ShippingMethodSeeder::class);
    $this->seed(ShieldPermissionSeeder::class);
});

test('store settings page renders and settings drive checkout totals', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();

    $this->actingAs($admin)->get('/admin/settings')
        ->assertOk()
        ->assertSee('Store identity');

    $settings = app(StoreSettings::class);
    $settings->update([
        'currency' => 'EUR',
        'tax_rate' => 10,
        'tax_inclusive' => true,
    ]);

    $product = Product::factory()->create(['price_cents' => 10000, 'currency' => 'USD']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price_cents' => 10000,
        'stock_quantity' => 2,
    ]);
    $cart = Cart::factory()->create(['user_id' => null, 'status' => 'active']);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $quote = app(CheckoutService::class)->quote(
        $cart->fresh(),
        null,
        ShippingMethod::query()->firstOrFail(),
    );

    expect($quote['currency'])->toBe('EUR')
        ->and($quote['tax_rate'])->toBe(0.1)
        ->and($quote['tax_cents'])->toBe(909);
});

test('all reports can export csv', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    foreach ([SalesReport::class, ProductReport::class, CustomerReport::class, InventoryReport::class] as $report) {
        Livewire::test($report)
            ->callAction('export')
            ->assertFileDownloaded();
    }
});

test('reports render paid sales data', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $customer = User::factory()->create(['name' => 'Report Customer']);
    $customer->assignRole('customer');
    $product = Product::query()->firstOrFail();
    $variant = $product->variants()->firstOrFail();
    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'customer_name' => 'Report Customer',
        'payment_status' => Order::PAYMENT_PAID,
        'status' => Order::STATUS_PAID,
        'placed_at' => now(),
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'line_total_cents' => 2000,
    ]);

    $this->actingAs($admin);

    $this->get(SalesReport::getUrl())->assertOk()->assertSee($order->number);
    $this->get(ProductReport::getUrl())->assertOk()->assertSee($product->name);
    $this->get(CustomerReport::getUrl())->assertOk()->assertSee('Report Customer');
    $this->get(InventoryReport::getUrl())->assertOk()->assertSee($variant->sku);
});

test('store settings page saves changes and audits them', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ManageStoreSettings::class)
        ->fillForm([
            'store_name' => 'RedEast Test',
            'currency' => 'GBP',
            'tax_rate' => 5,
            'tax_inclusive' => true,
            'order_prefix' => 'RT',
        ])
        ->call('save')
        ->assertNotified();

    expect(app(StoreSettings::class)->currency())->toBe('GBP')
        ->and(app(StoreSettings::class)->orderPrefix())->toBe('RT');

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'updated',
        'subject_type' => StoreSetting::class,
    ]);
});

test('reports, settings, staff, and activity log are reachable for an admin', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();

    $this->actingAs($admin);

    foreach ([
        SalesReport::getUrl(),
        ProductReport::getUrl(),
        CustomerReport::getUrl(),
        InventoryReport::getUrl(),
        ManageStoreSettings::getUrl(),
        StaffResource::getUrl(),
        ActivityLogResource::getUrl(),
    ] as $url) {
        $this->get($url)->assertOk();
    }
});

test('admin can create a staff member from the panel', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateStaff::class)
        ->fillForm([
            'name' => 'Ops Person',
            'email' => 'ops@redeast.agency',
            'password' => 'password',
            'roles' => [Role::findByName('staff', 'web')->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $staff = User::where('email', 'ops@redeast.agency')->firstOrFail();
    expect($staff->hasRole('staff'))->toBeTrue();
});

test('staff cannot access settings, reports, staff, or audit log', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff);

    $this->get(ManageStoreSettings::getUrl())->assertForbidden();
    $this->get(SalesReport::getUrl())->assertForbidden();
    $this->get(StaffResource::getUrl())->assertForbidden();
    $this->get(ActivityLogResource::getUrl())->assertForbidden();
});

test('customer cannot access the panel', function () {
    $customer = User::where('email', 'customer@redeast.agency')->firstOrFail();

    $this->actingAs($customer)->get('/admin')->assertForbidden();
});

test('admin actions are written to the activity log', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $this->actingAs($admin);

    $product = Product::factory()->create(['name' => 'Audit Product']);
    $product->update(['price_cents' => 4242]);

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'created',
        'subject_type' => Product::class,
        'subject_id' => $product->id,
    ]);

    $entry = ActivityLog::query()
        ->where('subject_type', Product::class)
        ->where('subject_id', $product->id)
        ->where('event', 'updated')
        ->firstOrFail();

    expect($entry->properties['attributes']['price_cents'])->toBe(4242);

    $this->get(ActivityLogResource::getUrl('view', ['record' => $entry]))->assertOk();
});

test('admin has app based two factor authentication available', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $admin->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

    $authentication = app(AppAuthentication::class);

    expect($authentication->isEnabled($admin))->toBeTrue()
        ->and($authentication->verifyCode($authentication->getCurrentCode($admin), $admin->getAppAuthenticationSecret()))->toBeTrue()
        ->and($admin->refresh()->getAppAuthenticationSecret())->toBe('JBSWY3DPEHPK3PXP');

    $this->assertDatabaseMissing('users', [
        'email' => $admin->email,
        'app_authentication_secret' => 'JBSWY3DPEHPK3PXP',
    ]);

    $this->actingAs($admin)->get('/admin/profile')->assertOk();
});

test('order payment history is visible on the order page', function () {
    $admin = User::where('email', 'admin@redeast.agency')->firstOrFail();
    $order = Order::factory()->create(['user_id' => null]);
    $order->payments()->create([
        'provider' => 'manual',
        'method' => 'cod',
        'status' => 'paid',
        'amount_cents' => $order->total_cents,
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/orders/'.$order->number)
        ->assertOk()
        ->assertSee('Payments');
});
