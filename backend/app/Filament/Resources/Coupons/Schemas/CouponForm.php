<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state) => Str::upper(trim((string) $state))),
                TextInput::make('description'),
                Select::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed amount',
                    ])
                    ->required()
                    ->default('percentage'),
                TextInput::make('value')
                    ->label('Value (percent or cents)')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('min_subtotal_cents')
                    ->label('Minimum subtotal (cents)')
                    ->numeric(),
                TextInput::make('max_discount_cents')
                    ->label('Maximum discount (cents)')
                    ->numeric(),
                TextInput::make('usage_limit')
                    ->label('Total usage limit')
                    ->numeric(),
                TextInput::make('per_user_limit')
                    ->label('Per-user limit')
                    ->numeric(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at')
                    ->after('starts_at'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
