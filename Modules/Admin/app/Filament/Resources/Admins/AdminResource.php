<?php

namespace Modules\Admin\Filament\Resources\Admins;

use Modules\Admin\Models\Admin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\Admins\Pages\CreateAdmin;
use Modules\Admin\Filament\Resources\Admins\Pages\EditAdmin;
use Modules\Admin\Filament\Resources\Admins\Pages\ListAdmins;
use Modules\Admin\Filament\Resources\Admins\Pages\ViewAdmin;
use Modules\Admin\Filament\Resources\Admins\Schemas\AdminForm;
use Modules\Admin\Filament\Resources\Admins\Schemas\AdminInfolist;
use Modules\Admin\Filament\Resources\Admins\Tables\AdminsTable;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdminInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        

            $query = parent::getEloquentQuery();

        if(auth('admin')->user()->hasRole('super_admin')){


        return $query
        ->onlyAdmins()
        ->with('branch');
        }

        return  $query;


    }    
      
  
    public static function getPages(): array
    {
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'view' => ViewAdmin::route('/{record}'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }
}
