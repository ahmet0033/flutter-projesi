<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\StoreSettings;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('orders_count')
                    ->label('Orders')
                    ->state(fn (User $record): int => $record->orders()->count()),
                TextEntry::make('lifetime_spend')
                    ->label('Lifetime spend')
                    ->state(fn (User $record): string => app(StoreSettings::class)->currency().' '.number_format($record->orders()->sum('total_cents') / 100, 2)),
                RepeatableEntry::make('orders')
                    ->label('Order history')
                    ->schema([
                        TextEntry::make('number')->label('Order'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('total_cents')->label('Total')->money(fn (): string => app(StoreSettings::class)->currency(), divideBy: 100),
                        TextEntry::make('placed_at')->label('Placed')->dateTime(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                RepeatableEntry::make('addresses')
                    ->label('Addresses')
                    ->schema([
                        TextEntry::make('label')->placeholder('Address'),
                        TextEntry::make('name'),
                        TextEntry::make('line1'),
                        TextEntry::make('city'),
                        TextEntry::make('country'),
                        TextEntry::make('phone'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
