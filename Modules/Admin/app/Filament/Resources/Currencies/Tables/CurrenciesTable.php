<?php

namespace Modules\Admin\Filament\Resources\Currencies\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('ISO Code')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Currency')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('symbol')
                    ->label('Symbol')
                    ->alignCenter(),

                TextColumn::make('exchange_rate')
                    ->label('Exchange Rate (to Base)')
                    ->numeric(decimalPlaces: 6)
                    ->sortable(),

                IconColumn::make('is_base')
                    ->label('Base Currency')
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('is_base', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                TernaryFilter::make('is_base')
                    ->label('Base Currency'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn ($record) => (bool) $record->is_base),
            ]);
    }
}
