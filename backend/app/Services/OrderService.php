<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Support\StoreSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CouponService $coupons,
        private readonly CheckoutService $checkout,
        private readonly StoreSettings $settings,
    ) {}

    public function place(?User $user, Cart $cart, array $data): Order
    {
        if (! empty($data['idempotency_key'])) {
            $existing = Order::query()
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing) {
                return $existing->load(['items', 'payments']);
            }
        }

        $order = DB::transaction(function () use ($user, $cart, $data) {
            $cart->load('items.variant.product');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $shipping = ShippingMethod::query()
                ->active()
                ->find($data['shipping_method_id'] ?? null);

            if (! $shipping) {
                throw ValidationException::withMessages([
                    'shipping_method_id' => 'Choose a valid shipping method.',
                ]);
            }

            $subtotal = (int) $cart->items->sum(fn ($item) => $item->lineTotalCents());
            $coupon = $this->coupons->findValid(
                $data['coupon_code'] ?? null,
                $user,
                $subtotal,
            );

            $quote = $this->checkout->quote($cart, $coupon, $shipping);

            $lockedVariants = [];
            foreach ($cart->items as $item) {
                $variant = ProductVariant::query()
                    ->with('product')
                    ->lockForUpdate()
                    ->find($item->product_variant_id);

                if (! $variant || ! $variant->is_active || ! $variant->product?->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'A product in your cart is no longer available.',
                    ]);
                }

                if ($item->quantity > $variant->stock_quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$variant->stock_quantity} left for {$variant->product->name}.",
                    ]);
                }

                $lockedVariants[$item->id] = $variant;
            }

            $paymentMethod = $data['payment_method'] ?? 'cod';

            if (! array_key_exists($paymentMethod, config('store.payment_methods', []))) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Choose a valid payment method.',
                ]);
            }

            $order = Order::create([
                'number' => $this->nextNumber(),
                'user_id' => $user?->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'payment_method' => $paymentMethod,
                'currency' => $quote['currency'],
                'subtotal_cents' => $quote['subtotal_cents'],
                'discount_cents' => $quote['discount_cents'],
                'shipping_cents' => $quote['shipping_cents'],
                'tax_cents' => $quote['tax_cents'],
                'total_cents' => $quote['total_cents'],
                'coupon_id' => $coupon?->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $variant = $lockedVariants[$item->id];
                $unitPrice = $variant->effectivePriceCents();

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'sku' => $variant->sku,
                    'unit_price_cents' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total_cents' => $unitPrice * $item->quantity,
                    'specs' => $variant->product->specs,
                ]);

                $variant->decrement('stock_quantity', $item->quantity);

                InventoryMovement::create([
                    'product_variant_id' => $variant->id,
                    'quantity_delta' => -$item->quantity,
                    'reason' => 'order',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'note' => "Order {$order->number}",
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'manual',
                'method' => $paymentMethod,
                'status' => 'pending',
                'amount_cents' => $order->total_cents,
            ]);

            if ($coupon) {
                $this->coupons->recordUsage($coupon, $user, $order);
            }

            $cart->items()->delete();

            return $order->load(['items', 'payments']);
        });

        $user?->notify(new OrderPlacedNotification($order));

        return $order;
    }

    public function markPaid(Order $order, ?string $transactionId = null): Order
    {
        if ($order->payment_status === Order::PAYMENT_PAID) {
            return $order;
        }

        DB::transaction(function () use ($order, $transactionId) {
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => $order->status === Order::STATUS_PENDING
                    ? Order::STATUS_PROCESSING
                    : $order->status,
            ]);

            $order->payments()->where('status', 'pending')->update([
                'status' => 'paid',
                'transaction_id' => $transactionId,
                'paid_at' => now(),
            ]);
        });

        $order = $order->fresh(['items', 'payments']);
        $order->user?->notify(new OrderStatusUpdatedNotification($order, 'Ödendi'));

        return $order;
    }

    public function cancel(Order $order): Order
    {
        if (! $order->canCancel()) {
            throw ValidationException::withMessages(['order' => 'This order cannot be cancelled.']);
        }

        DB::transaction(function () use ($order) {
            $order->load('items.variant');

            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);

                    InventoryMovement::create([
                        'product_variant_id' => $item->variant->id,
                        'quantity_delta' => $item->quantity,
                        'reason' => 'cancel',
                        'reference_type' => Order::class,
                        'reference_id' => $order->id,
                        'note' => "Cancelled {$order->number}",
                    ]);
                }
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => $order->payment_status === Order::PAYMENT_PAID
                    ? Order::PAYMENT_REFUNDED
                    : Order::PAYMENT_PENDING,
            ]);

            $order->payments()->where('status', 'pending')->update(['status' => 'cancelled']);
        });

        $order = $order->fresh(['items', 'payments']);
        $order->user?->notify(new OrderStatusUpdatedNotification($order, 'İptal edildi'));

        return $order;
    }

    public function refund(Order $order): Order
    {
        if (! $order->canRefund()) {
            throw ValidationException::withMessages(['order' => 'This order cannot be refunded.']);
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => Order::STATUS_REFUNDED,
                'payment_status' => Order::PAYMENT_REFUNDED,
            ]);

            $order->payments()->update(['status' => 'refunded']);
        });

        $order = $order->fresh(['items', 'payments']);
        $order->user?->notify(new OrderStatusUpdatedNotification($order, 'İade edildi'));

        return $order;
    }

    private function nextNumber(): string
    {
        $prefix = $this->settings->orderPrefix();

        do {
            $number = $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
