<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Support\StoreSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->where('idempotency_key', 'like', 'demo-%')->exists()) {
            return;
        }

        $customer = User::query()->where('email', 'customer@redeast.agency')->first();
        $admin = User::query()->where('email', 'admin@redeast.agency')->first();
        $shipping = ShippingMethod::query()->where('code', 'standard')->first();
        $settings = app(StoreSettings::class);
        $currency = $settings->currency();
        $taxRate = $settings->taxRate();
        $taxInclusive = $settings->taxInclusive();

        if (! $customer) {
            return;
        }

        $products = Product::query()
            ->active()
            ->whereIn('slug', ['nova-x', 'nova-air', 'atlas-14', 'nova-mini', 'nova-x-pro', 'atlas-16-pro'])
            ->get()
            ->keyBy('slug');

        if ($products->count() < 6) {
            return;
        }

        $variants = ProductVariant::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $definitions = [
            [
                'key' => 'demo-1',
                'number' => 'RE-DEMO-001',
                'days_ago' => 6,
                'status' => Order::STATUS_DELIVERED,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'bank_transfer',
                'items' => [['nova-x', 2], ['nova-air', 1]],
            ],
            [
                'key' => 'demo-2',
                'number' => 'RE-DEMO-002',
                'days_ago' => 2,
                'status' => Order::STATUS_SHIPPED,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'bank_transfer',
                'items' => [['atlas-14', 1]],
            ],
            [
                'key' => 'demo-3',
                'number' => 'RE-DEMO-003',
                'days_ago' => 1,
                'status' => Order::STATUS_PROCESSING,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'bank_transfer',
                'items' => [['nova-mini', 3]],
            ],
            [
                'key' => 'demo-4',
                'number' => 'RE-DEMO-004',
                'days_ago' => 0,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'payment_method' => 'cod',
                'items' => [['nova-x-pro', 1]],
            ],
            [
                'key' => 'demo-5',
                'number' => 'RE-DEMO-005',
                'days_ago' => 12,
                'status' => Order::STATUS_REFUNDED,
                'payment_status' => Order::PAYMENT_REFUNDED,
                'payment_method' => 'bank_transfer',
                'items' => [['atlas-16-pro', 1]],
            ],
            [
                'key' => 'demo-6',
                'number' => 'RE-DEMO-006',
                'days_ago' => 20,
                'status' => Order::STATUS_PROCESSING,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'bank_transfer',
                'items' => [['nova-air', 2]],
            ],
        ];

        $orders = Model::withoutEvents(fn () => DB::transaction(function () use (
            $definitions,
            $customer,
            $products,
            $variants,
            $shipping,
            $currency,
            $taxRate,
            $taxInclusive,
        ): array {
            $created = [];

            foreach ($definitions as $definition) {
                $placedAt = now()->subDays($definition['days_ago'])->setTime(12, 0);
                $items = [];
                $subtotal = 0;

                foreach ($definition['items'] as [$slug, $quantity]) {
                    $product = $products[$slug];
                    $variant = $variants[$product->id] ?? null;
                    $unitPrice = $variant?->price_cents ?? $product->price_cents;
                    $lineTotal = $unitPrice * $quantity;

                    $items[] = [
                        'product' => $product,
                        'variant' => $variant,
                        'quantity' => $quantity,
                        'unit_price_cents' => $unitPrice,
                        'line_total_cents' => $lineTotal,
                    ];

                    $subtotal += $lineTotal;
                }

                $shippingCents = $shipping
                    ? ($shipping->free_over_cents === null || $subtotal < $shipping->free_over_cents
                        ? $shipping->price_cents
                        : 0)
                    : 0;
                $taxCents = $taxRate > 0
                    ? (int) round($taxInclusive
                        ? $subtotal * $taxRate / (1 + $taxRate)
                        : $subtotal * $taxRate)
                    : 0;
                $total = $subtotal + $shippingCents + ($taxInclusive ? 0 : $taxCents);

                $order = Order::query()->create([
                    'number' => $definition['number'],
                    'user_id' => $customer->id,
                    'status' => $definition['status'],
                    'payment_status' => $definition['payment_status'],
                    'payment_method' => $definition['payment_method'],
                    'currency' => $currency,
                    'subtotal_cents' => $subtotal,
                    'discount_cents' => 0,
                    'shipping_cents' => $shippingCents,
                    'tax_cents' => $taxCents,
                    'total_cents' => $total,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => '+905551112233',
                    'shipping_address' => [
                        'name' => $customer->name,
                        'phone' => '+905551112233',
                        'line1' => 'Demo Street 12',
                        'city' => 'Mersin',
                        'state' => 'Mersin',
                        'postal_code' => '33000',
                        'country' => 'TR',
                    ],
                    'billing_address' => null,
                    'notes' => 'Demo order seeded for the dashboard.',
                    'idempotency_key' => $definition['key'],
                    'placed_at' => $placedAt,
                ]);

                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'product_variant_id' => $item['variant']?->id,
                        'product_name' => $item['product']->name,
                        'sku' => $item['variant']?->sku ?? 'DEMO-'.$item['product']->id,
                        'unit_price_cents' => $item['unit_price_cents'],
                        'quantity' => $item['quantity'],
                        'line_total_cents' => $item['line_total_cents'],
                        'specs' => $item['product']->specs,
                    ]);
                }

                $paymentStatus = match ($definition['payment_status']) {
                    Order::PAYMENT_PAID => 'paid',
                    Order::PAYMENT_REFUNDED => 'refunded',
                    default => 'pending',
                };

                Payment::query()->create([
                    'order_id' => $order->id,
                    'provider' => 'manual',
                    'method' => $definition['payment_method'],
                    'status' => $paymentStatus,
                    'amount_cents' => $total,
                    'transaction_id' => $paymentStatus === 'pending' ? null : 'DEMO-'.strtoupper($definition['key']),
                    'paid_at' => $paymentStatus === 'pending' ? null : $placedAt,
                ]);

                $created[] = $order;
            }

            ProductVariant::query()
                ->where('sku', 'RE-NOVA-MINI')
                ->update(['stock_quantity' => 4]);

            return $created;
        }));

        foreach (array_slice($orders, 0, 3) as $order) {
            ActivityLog::query()->create([
                'user_id' => $admin?->id,
                'event' => 'created',
                'subject_type' => Order::class,
                'subject_id' => $order->id,
                'description' => "Order {$order->number} seeded",
                'properties' => [
                    'attributes' => [
                        'number' => $order->number,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'total_cents' => $order->total_cents,
                    ],
                ],
                'created_at' => $order->placed_at,
            ]);
        }
    }
}
