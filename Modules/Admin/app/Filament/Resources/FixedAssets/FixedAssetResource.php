<?php

namespace Modules\Admin\Filament\Resources\FixedAssets;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\FixedAsset;
use Modules\Admin\Filament\Resources\FixedAssets\Pages\CreateFixedAsset;
use Modules\Admin\Filament\Resources\FixedAssets\Pages\EditFixedAsset;
use Modules\Admin\Filament\Resources\FixedAssets\Pages\ListFixedAssets;
use Modules\Admin\Filament\Resources\FixedAssets\Schemas\FixedAssetForm;
use Modules\Admin\Filament\Resources\FixedAssets\Tables\FixedAssetsTable;
use UnitEnum;

class FixedAssetResource extends Resource
{
    protected static ?string $model = FixedAsset::class;

    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Fixed Assets';

    protected static ?string $recordTitleAttribute = 'asset_number';

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('create_journal_entries', 'admin')) : false;
    }

    public static function form(Schema $schema): Schema
    {
        return FixedAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FixedAssetsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['assetAccount', 'depreciationExpenseAccount', 'accumulatedDepreciationAccount', 'branch']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFixedAssets::route('/'),
            'create' => CreateFixedAsset::route('/create'),
            'edit'   => EditFixedAsset::route('/{record}/edit'),
        ];
    }
}
