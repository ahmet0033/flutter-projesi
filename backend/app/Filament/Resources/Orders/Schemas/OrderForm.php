<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Support\StoreSettings;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_method')
                    ->required(),
                TextInput::make('currency')
                    ->required()
                    ->default(fn (): string => app(StoreSettings::class)->currency()),
                TextInput::make('subtotal_cents')
                    ->required()
                    ->numeric(),
                TextInput::make('discount_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('shipping_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_cents')
                    ->required()
                    ->numeric(),
                Select::make('coupon_id')
                    ->relationship('coupon', 'id'),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_email')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel(),
                Textarea::make('shipping_address')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('billing_address')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('idempotency_key'),
                DateTimePicker::make('placed_at'),
            ]);
    }
}
