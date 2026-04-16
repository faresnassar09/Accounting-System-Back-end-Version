<?php

namespace Modules\Admin\Filament\Resources\AccountingSettnings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountingSettningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('label')
                ->label("Label"),
                TextColumn::make('account.name')
                ->label('Asscoiated Account'),

                TextColumn::make('description')
                ->label('Description')

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([

            ]);
    }
}
