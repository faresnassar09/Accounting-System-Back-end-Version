<?php

namespace Modules\Admin\Filament\Resources\AuditTrail;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Models\AccountingAuditLog;
use Modules\Admin\Filament\Resources\AuditTrail\Pages\ListAuditLogs;
use Modules\Admin\Filament\Resources\AuditTrail\Pages\ViewAuditLog;
use Modules\Admin\Filament\Resources\AuditTrail\Schemas\AuditTrailInfolist;
use Modules\Admin\Filament\Resources\AuditTrail\Tables\AuditTrailTable;
use UnitEnum;

class AuditTrailResource extends Resource
{
    protected static ?string $model = AccountingAuditLog::class;

    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Audit Trail';

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasRole('auditor') || $user->hasPermissionTo('view_reports', 'admin')) : false;
    }

    public static function canCreate(): bool
    {
        return false; // Audit logs are immutable
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Audit logs cannot be modified
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Audit logs cannot be deleted
    }

    public static function table(Table $table): Table
    {
        return AuditTrailTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditTrailInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'view'  => ViewAuditLog::route('/{record}'),
        ];
    }
}
