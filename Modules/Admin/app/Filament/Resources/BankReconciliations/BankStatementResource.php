<?php

namespace Modules\Admin\Filament\Resources\BankReconciliations;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\BankStatement;
use Modules\Admin\Filament\Resources\BankReconciliations\Pages\CreateBankStatement;
use Modules\Admin\Filament\Resources\BankReconciliations\Pages\ListBankStatements;
use Modules\Admin\Filament\Resources\BankReconciliations\Pages\ReconcileBankStatement;
use Modules\Admin\Filament\Resources\BankReconciliations\Schemas\BankStatementForm;
use Modules\Admin\Filament\Resources\BankReconciliations\Tables\BankStatementsTable;
use UnitEnum;

class BankStatementResource extends Resource
{
    protected static ?string $model = BankStatement::class;

    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Bank Reconciliation';

    protected static ?string $recordTitleAttribute = 'statement_date';

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('create_journal_entries', 'admin')) : false;
    }

    public static function form(Schema $schema): Schema
    {
        return BankStatementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankStatementsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['account', 'lines']);
    }

    public static function getPages(): array
    {
        return [
            'index'     => ListBankStatements::route('/'),
            'create'    => CreateBankStatement::route('/create'),
            'reconcile' => ReconcileBankStatement::route('/{record}/reconcile'),
        ];
    }
}
