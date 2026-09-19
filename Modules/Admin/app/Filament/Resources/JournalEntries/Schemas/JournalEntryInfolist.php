<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JournalEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reference')
                    ->label('Reference')
                    ->weight('bold'),

                TextEntry::make('date')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i'),

                TextEntry::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'journal'    => 'info',
                        'opening'    => 'warning',
                        'closing'    => 'gray',
                        'adjustment' => 'purple',
                        default      => 'gray',
                    }),

                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'draft'    => 'warning',
                        'cancled'  => 'danger',
                        default    => 'gray',
                    }),

                TextEntry::make('total_debit')
                    ->label('Total Debit')
                    ->numeric(decimalPlaces: 2),

                TextEntry::make('total_credit')
                    ->label('Total Credit')
                    ->numeric(decimalPlaces: 2),

                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                RepeatableEntry::make('lines')
                    ->label('Journal Entry Lines')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('account.number')
                            ->label('Account #'),

                        TextEntry::make('account.name')
                            ->label('Account Name'),

                        TextEntry::make('debit')
                            ->label('Debit')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('credit')
                            ->label('Credit')
                            ->numeric(decimalPlaces: 2),

                        TextEntry::make('source_type')
                            ->label('Source'),
                    ])
                    ->columns(5),
            ]);
    }
}
