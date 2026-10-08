<?php

namespace Modules\Admin\Filament\Resources\Admins\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('branch.name')
                ->label('Branch Name'),
                TextColumn::make('branch.code')
                ->label('Branch Code'),
                TextColumn::make('created_at')
                ->dateTime(),

                ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
                ]),
            ]);
    }
}
