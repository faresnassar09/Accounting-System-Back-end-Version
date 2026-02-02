<?php

namespace Modules\Admin\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{

 

    public static function configure(Table $table): Table
    {
        
        return $table
            ->columns([

                TextColumn::make('name'),
                TextColumn::make('number'),
                TextColumn::make('initial_balance'),
                // TextColumn::make('parent.name')
                // ->label('Blongs To')
                // ->getStateUsing(fn ($record) => $record->parent?->name ?? 'N/A'),

                TextColumn::make('children_count_column')
                ->label('Has Accounts No.'),
                
                
                TextColumn::make('description')
                ->getStateUsing(fn ($record) => $record->description ?? 'N/A'),

                TextColumn::make('created_at')
                ->dateTime()
                ->searchable()
                ->sortable(),


])
              ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),                
                DeleteAction::make()
                ->before(function($record,$action){


                    if ($record->children()->exists()) {
                        Notification::make()
                        ->title("Cant't Delete")
                        ->body('this Account Has Another Sup Accounts Delete Related Accounts Before')
                        ->danger()
                        ->send(); 

                        $action->cancel();

                    }

                } ),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }


    
}