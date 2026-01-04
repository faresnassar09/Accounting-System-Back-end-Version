<?php

namespace Modules\Admin\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {             
        return $table
            ->columns([


                TextColumn::make('name')
                ->searchable(),

                TextColumn::make('address')
                ->searchable(),
                
                TextColumn::make('phone')
                ->searchable(),

                TextColumn::make('code')
                ->searchable(),

                TextColumn::make('users_count')
                ->searchable(),
                
                TextColumn::make('admins_count')
                ->searchable(),

                ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
