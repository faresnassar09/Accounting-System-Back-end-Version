<?php

namespace Modules\Admin\Filament\Resources\Budgets;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\Budget;
use Modules\Admin\Filament\Resources\Budgets\Pages\CreateBudget;
use Modules\Admin\Filament\Resources\Budgets\Pages\EditBudget;
use Modules\Admin\Filament\Resources\Budgets\Pages\ListBudgets;
use Modules\Admin\Filament\Resources\Budgets\Schemas\BudgetForm;
use Modules\Admin\Filament\Resources\Budgets\Tables\BudgetsTable;
use UnitEnum;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Budgets & Planning';

    protected static ?string $recordTitleAttribute = 'fiscal_year';

    public static function form(Schema $schema): Schema
    {
        return BudgetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['account', 'branch']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBudgets::route('/'),
            'create' => CreateBudget::route('/create'),
            'edit'   => EditBudget::route('/{record}/edit'),
        ];
    }
}
