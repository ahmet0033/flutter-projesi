<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('event')->badge(),
            TextEntry::make('description')->columnSpanFull(),
            TextEntry::make('user.name')->label('User')->placeholder('System'),
            TextEntry::make('subject_type')
                ->label('Subject')
                ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
            TextEntry::make('subject_id')->label('Subject ID')->placeholder('-'),
            TextEntry::make('ip_address')->label('IP address')->placeholder('-'),
            KeyValueEntry::make('properties')
                ->label('Changes')
                ->columnSpanFull(),
        ])->columns(2);
    }
}
