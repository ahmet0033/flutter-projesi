<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('price_cents')
                    ->label('Price (cents)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('free_over_cents')
                    ->label('Free over subtotal (cents)')
                    ->numeric()
                    ->minValue(0),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
