<?php

namespace Modules\Admin\Filament\Resources\M2MLogins\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Modules\Authorization\Models\Passport\ClientToken;

class M2MLoginInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client.name')
                    ->label('External Client / Service')
                    ->weight('bold'),

                TextEntry::make('client_id')
                    ->label('Client ID (UUID)')
                    ->fontFamily('mono')
                    ->copyable(),

                TextEntry::make('status')
                    ->label('Token Lifecycle Status')
                    ->badge()
                    ->getStateUsing(fn (ClientToken $record) => match (true) {
                        $record->revoked => 'Revoked',
                        $record->expires_at && $record->expires_at->isPast() => 'Expired',
                        default => 'Active Token',
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Active') => 'success',
                        str_contains($state, 'Revoked') => 'danger',
                        default => 'warning',
                    }),

                TextEntry::make('created_at')
                    ->label('Authenticated / Token Issued At')
                    ->dateTime('Y-m-d H:i:s'),

                TextEntry::make('revoked_at')
                    ->label('Revoked At')
                    ->getStateUsing(function (ClientToken $record) {
                        if ($record->revoked) {
                            return $record->updated_at?->format('Y-m-d H:i:s') . ' (' . $record->updated_at?->diffForHumans() . ')';
                        }
                        if ($record->expires_at && $record->expires_at->isPast()) {
                            return 'Expired (' . $record->expires_at?->format('Y-m-d H:i:s') . ')';
                        }
                        return 'Token Currently Active';
                    }),

                TextEntry::make('expires_at')
                    ->label('Token Expires At')
                    ->dateTime('Y-m-d H:i:s'),

                TextEntry::make('scopes')
                    ->label('Granted Scopes')
                    ->badge()
                    ->placeholder('All Scopes (*)'),

                TextEntry::make('id')
                    ->label('Token Hash ID')
                    ->fontFamily('mono')
                    ->copyable(),
            ]);
    }
}
