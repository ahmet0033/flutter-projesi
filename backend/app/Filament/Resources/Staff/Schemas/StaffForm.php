<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(120),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText('Leave blank to keep the current password.'),
            Select::make('roles')
                ->relationship(
                    'roles',
                    'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query->whereIn('name', ['admin', 'staff']),
                )
                ->multiple()
                ->preload()
                ->required()
                ->helperText('Admin has full operational access; staff is limited to catalog, orders, and reviews.'),
        ]);
    }
}
