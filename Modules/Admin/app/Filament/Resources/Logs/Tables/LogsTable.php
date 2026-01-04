<?php

namespace Modules\Admin\Filament\Resources\Logs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('description')
                ->sortable()
                ->searchable(),

                TextColumn::make('causer.name')
                ->label('actor')
                ->searchable(),
            
                TextColumn::make('causer.roles.0.name')
                ->label('Actor Role')
                ->badge(),

                TextColumn::make('causer.branch.name')
                ->label('Branch')
                ->searchable(),

                TextColumn::make('subject.name')
                ->label('Subject Name'),

                TextColumn::make('created_at')
                ->dateTime(),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                ->visible(fn () => current_guard_user()->hasRole('super_admin')),
                
            ])
            ->toolbarActions([

            ]);
    }
}
