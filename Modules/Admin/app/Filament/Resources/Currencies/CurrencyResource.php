<?php

namespace Modules\Admin\Filament\Resources\Currencies;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Accounting\Models\Currency;
use Modules\Admin\Filament\Resources\Currencies\Pages\CreateCurrency;
use Modules\Admin\Filament\Resources\Currencies\Pages\EditCurrency;
use Modules\Admin\Filament\Resources\Currencies\Pages\ListCurrencies;
use Modules\Admin\Filament\Resources\Currencies\Schemas\CurrencyForm;
use Modules\Admin\Filament\Resources\Currencies\Tables\CurrenciesTable;
use UnitEnum;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Currencies & Rates';

    protected static ?string $recordTitleAttribute = 'code';

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('manage_currencies', 'admin')) : false;
    }

    public static function canCreate(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('manage_currencies', 'admin')) : false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('manage_currencies', 'admin')) : false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth('admin')->user();

        if ($record instanceof Currency && $record->is_base) {
            return false;
        }

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('manage_currencies', 'admin')) : false;
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrenciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit'   => EditCurrency::route('/{record}/edit'),
        ];
    }
}
