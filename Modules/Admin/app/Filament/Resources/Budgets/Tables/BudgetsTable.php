<?php

namespace Modules\Admin\Filament\Resources\Budgets\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Branch\Models\Branch;

class BudgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fiscal_year')
                    ->label('Fiscal Year')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('account.name')
                    ->label('Account')
                    ->formatStateUsing(fn ($record) => $record->account ? "#{$record->account->number} — {$record->account->name}" : '—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->default('Consolidated')
                    ->badge()
                    ->color(fn ($state) => $state === 'Consolidated' ? 'gray' : 'info')
                    ->sortable(),

                TextColumn::make('allocated_amount')
                    ->label('Allocated Budget')
                    ->money('USD')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fiscal_year', 'desc')
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
