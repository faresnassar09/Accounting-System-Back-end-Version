<?php

namespace Modules\Admin\Filament\Resources\UserLogins\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Authorization\Models\Passport\UserToken;

class UserLoginInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User Name')
                    ->weight('bold'),

                TextEntry::make('user.email')
                    ->label('User Email')
                    ->copyable(),

                TextEntry::make('user.branch.name')
                    ->label('Branch')
                    ->placeholder('None assigned'),

                TextEntry::make('user.team.name')
                    ->label('Team')
                    ->placeholder('None assigned'),

                TextEntry::make('status')
                    ->label('Session Status')
                    ->badge()
                    ->getStateUsing(fn (UserToken $record) => match (true) {
                        $record->revoked => 'Logged Out (Revoked)',
                        $record->expires_at && $record->expires_at->isPast() => 'Expired',
                        default => 'Active / Online',
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Active') => 'success',
                        str_contains($state, 'Logged Out') => 'gray',
                        default => 'warning',
                    }),

                TextEntry::make('created_at')
                    ->label('Logged In At')
                    ->dateTime('Y-m-d H:i:s'),

                TextEntry::make('logout_time')
                    ->label('Logged Out At')
                    ->getStateUsing(function (UserToken $record) {
                        if ($record->revoked) {
                            return $record->updated_at?->format('Y-m-d H:i:s') . ' (' . $record->updated_at?->diffForHumans() . ')';
                        }
                        if ($record->expires_at && $record->expires_at->isPast()) {
                            return 'Expired (' . $record->expires_at?->format('Y-m-d H:i:s') . ')';
                        }
                        return 'Session Still Active';
                    }),

                TextEntry::make('expires_at')
                    ->label('Token Expires At')
                    ->dateTime('Y-m-d H:i:s'),

                TextEntry::make('client.name')
                    ->label('Authenticated Via Client')
                    ->placeholder('Personal Access'),

                TextEntry::make('id')
                    ->label('Access Token ID')
                    ->fontFamily('mono')
                    ->copyable(),
            ]);
    }
}
