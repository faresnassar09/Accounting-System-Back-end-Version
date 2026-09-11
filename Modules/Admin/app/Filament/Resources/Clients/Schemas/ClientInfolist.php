<?php

namespace Modules\Admin\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Client Name')
                    ->weight('bold'),

                TextEntry::make('id')
                    ->label('Client ID')
                    ->copyable()
                    ->copyMessage('Client ID copied to clipboard')
                    ->fontFamily('mono'),

                TextEntry::make('grant_types')
                    ->label('Allowed Grant Types')
                    ->badge()
                    ->separator(','),

                TextEntry::make('redirect_uris')
                    ->label('Redirect URIs')
                    ->placeholder('None configured')
                    ->badge()
                    ->separator(','),

                IconEntry::make('revoked')
                    ->label('Active Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => !$record->revoked),

                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ]);
    }
}
