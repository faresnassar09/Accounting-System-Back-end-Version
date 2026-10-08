<?php

namespace Modules\Admin\Filament\Resources\Roles;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\Roles\Pages\CreateRole;
use Modules\Admin\Filament\Resources\Roles\Pages\EditRole;
use Modules\Admin\Filament\Resources\Roles\Pages\ListRoles;
use Modules\Admin\Filament\Resources\Roles\Pages\ViewRole;
use Modules\Admin\Filament\Resources\Roles\Schemas\RoleForm;
use Modules\Admin\Filament\Resources\Roles\Schemas\RoleInfolist;
use Modules\Admin\Filament\Resources\Roles\Tables\RolesTable;
use Modules\Authorization\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\PermissionsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
    
        return parent::getEloquentQuery()  
        ->withCount('users');

    
    
    }
        
    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
