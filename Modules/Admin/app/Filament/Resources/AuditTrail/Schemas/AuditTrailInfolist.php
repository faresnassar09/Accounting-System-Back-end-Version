<?php

namespace Modules\Admin\Filament\Resources\AuditTrail\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditTrailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('Y-m-d H:i:s'),

                TextEntry::make('event')
                    ->label('Event Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created'    => 'success',
                        'updated'    => 'info',
                        'reversed'   => 'danger',
                        'deleted'    => 'danger',
                        'closed'     => 'purple',
                        'reconciled' => 'primary',
                        default      => 'gray',
                    }),

                TextEntry::make('auditable_type')
                    ->label('Audited Model')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                TextEntry::make('auditable_id')
                    ->label('Record ID'),

                TextEntry::make('user_name')
                    ->label('Authenticated User / Actor'),

                TextEntry::make('ip_address')
                    ->label('Remote IP Address'),

                TextEntry::make('description')
                    ->label('Action Description / Narration')
                    ->columnSpanFull(),

                TextEntry::make('old_values')
                    ->label('Before Change (Original Values)')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'None')
                    ->columnSpanFull(),

                TextEntry::make('new_values')
                    ->label('After Change (Applied Values)')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'None')
                    ->columnSpanFull(),
            ]);
    }
}
