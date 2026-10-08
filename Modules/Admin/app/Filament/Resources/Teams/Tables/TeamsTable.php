<?php

namespace Modules\Admin\Filament\Resources\Teams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                ->searchable()
                ->sortable(),

                TextColumn::make('users_count')
                ->label('Members Count'),

                ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                ->before(function ($record, $action) {

                    if ($record->users()->exists()) {
                    
                    Notification::make()
                        ->title('Cannot delete team')
                        ->body('This team has users assigned to it. Please remove or reassign them first.')
                        ->danger()
                        ->send();
  
                        $action->cancel();
                    }
                }),

                ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
