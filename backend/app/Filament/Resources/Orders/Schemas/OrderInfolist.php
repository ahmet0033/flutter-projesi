<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Support\StoreSettings;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('number')->label('Order'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'gray',
                        Order::STATUS_PAID, Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_PROCESSING => 'warning',
                        Order::STATUS_SHIPPED => 'info',
                        Order::STATUS_CANCELLED, Order::STATUS_REFUNDED => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('payment_status')->badge(),
                TextEntry::make('customer_name')->label('Customer'),
                TextEntry::make('customer_email')->label('Email'),
                TextEntry::make('customer_phone')->label('Phone')->placeholder('-'),
                TextEntry::make('placed_at')->dateTime(),
                TextEntry::make('coupon.code')->label('Coupon')->placeholder('-'),
                TextEntry::make('total_cents')->label('Total')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                TextEntry::make('subtotal_cents')->label('Subtotal')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                TextEntry::make('discount_cents')->label('Discount')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                TextEntry::make('shipping_cents')->label('Shipping')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                TextEntry::make('tax_cents')->label('Tax included')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                TextEntry::make('notes')->columnSpanFull()->placeholder('-'),
                KeyValueEntry::make('shipping_address')
                    ->label('Shipping address')
                    ->columnSpanFull(),
                KeyValueEntry::make('billing_address')
                    ->label('Billing address')
                    ->columnSpanFull(),
                RepeatableEntry::make('items')
                    ->label('Items')
                    ->schema([
                        TextEntry::make('product_name')->label('Product'),
                        TextEntry::make('sku')->label('SKU'),
                        TextEntry::make('quantity'),
                        TextEntry::make('unit_price_cents')->label('Unit')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                        TextEntry::make('line_total_cents')->label('Line total')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
                RepeatableEntry::make('payments')
                    ->label('Payments')
                    ->schema([
                        TextEntry::make('provider'),
                        TextEntry::make('method'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('amount_cents')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                        TextEntry::make('transaction_id')->placeholder('-'),
                        TextEntry::make('paid_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),
            ]);
    }
}
