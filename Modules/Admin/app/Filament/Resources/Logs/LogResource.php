<?php

namespace Modules\Admin\Filament\Resources\Logs;

use BackedEnum;
use Filament\Forms\Components\ViewField;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Filament\Resources\Logs\Pages\CreateLog;
use Modules\Admin\Filament\Resources\Logs\Pages\ListLogs;
use Modules\Admin\Filament\Resources\Logs\Pages\ViewLog;
use Modules\Admin\Filament\Resources\Logs\Schemas\LogForm;
use Modules\Admin\Filament\Resources\Logs\Tables\LogsTable;
use Modules\Branch\Models\BranchScope;
use Spatie\Activitylog\Models\Activity;

class LogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Log';

    public static function form(Schema $schema): Schema
    {
        return LogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogsTable::configure($table);
    }


    public static function canDelete(Model $record): bool
    {
        return current_guard_user()->hasRole('super_admin');
    }
    
        public static function getEloquentQuery(): Builder
    {
    
        if (current_guard_user()->hasRole('super_admin')) {
    
          return parent::getEloquentQuery()
          ->with(['causer.roles','causer.branch','subject'])
        ;
        }
    
        return parent::getEloquentQuery()
        ->with(['causer.roles','causer.branch','subject']);
    
    }
  
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLogs::route('/'),
            'view' => ViewLog::route('/{record}/view')
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('description'),
                TextEntry::make('causer.name'),
                TextEntry::make('causer_type')
                ->label('Causer Role')
                ->getStateUsing(fn ($record) => class_basename($record->causer_type)),

                TextEntry::make('event')
                ->label('Action'),

                TextEntry::make('subject_type')
                ->getStateUsing(fn ($record) => class_basename($record->subject_type)),

                TextEntry::make('subject.name')
                ->label('Subject Name'),

                TextEntry::make('properties')
                ->label('data'),

                TextEntry::make('created_at')
                ->dateTime()

                ->columnSpanFull(),
            ]);
    }



}
