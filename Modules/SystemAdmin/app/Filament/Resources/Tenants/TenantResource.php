<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\SystemAdmin\Filament\Resources\Tenants\Pages\CreateTenant;
use Modules\SystemAdmin\Filament\Resources\Tenants\Pages\EditTenant;
use Modules\SystemAdmin\Filament\Resources\Tenants\Pages\ListTenants;
use Modules\SystemAdmin\Filament\Resources\Tenants\Pages\ViewTenant;
use Modules\SystemAdmin\Filament\Resources\Tenants\Schemas\TenantForm;
use Modules\SystemAdmin\Filament\Resources\Tenants\Schemas\TenantInfolist;
use Modules\SystemAdmin\Filament\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
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
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'view' => ViewTenant::route('/{record}'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
