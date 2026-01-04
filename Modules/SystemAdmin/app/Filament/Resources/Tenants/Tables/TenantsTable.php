<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Forms\Components\JsonEditor;
use Tables\Columns\KeyValueColumn;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('organization_name'),
                TextColumn::make('organization_industry'),
                TextColumn::make('organization_address'),
                TextColumn::make('tenancy_db_name')
                ->label('Database Name'),


            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
              
            ]);
    }
}
