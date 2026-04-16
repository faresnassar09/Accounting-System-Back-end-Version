<?php

namespace Modules\Admin\Filament\Resources\AccountingSettnings;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\AccountingMapping;
use Modules\Admin\Filament\Resources\AccountingSettnings\Pages\CreateAccountingSettnings;
use Modules\Admin\Filament\Resources\AccountingSettnings\Pages\EditAccountingSettnings;
use Modules\Admin\Filament\Resources\AccountingSettnings\Pages\ListAccountingSettnings;
use Modules\Admin\Filament\Resources\AccountingSettnings\Pages\ViewAccountingSettnings;
use Modules\Admin\Filament\Resources\AccountingSettnings\Schemas\AccountingSettningsForm;
use Modules\Admin\Filament\Resources\AccountingSettnings\Schemas\AccountingSettningsInfolist;
use Modules\Admin\Filament\Resources\AccountingSettnings\Tables\AccountingSettningsTable;

class AccountingSettningsResource extends Resource
{
    protected static ?string $model = AccountingMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'AccountingSettnings';

    public static function form(Schema $schema): Schema
    {
        return AccountingSettningsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountingSettningsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountingSettningsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
{

    return parent::getEloquentQuery()  
    ->with('account');
    
}

    public static function getPages(): array
    {
        return [
            'index' => ListAccountingSettnings::route('/'),
            'view' => ViewAccountingSettnings::route('/{record}'),
            'edit' => EditAccountingSettnings::route('/{record}/edit'),
        ];
    }
}
