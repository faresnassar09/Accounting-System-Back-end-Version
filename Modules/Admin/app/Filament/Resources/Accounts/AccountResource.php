<?php

namespace Modules\Admin\Filament\Resources\Accounts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\Account;
use Modules\Admin\Filament\Resources\Accounts\Pages\CreateAccount;
use Modules\Admin\Filament\Resources\Accounts\Pages\EditAccount;
use Modules\Admin\Filament\Resources\Accounts\Pages\ListAccounts;
use Modules\Admin\Filament\Resources\Accounts\Pages\ViewAccount;
use Modules\Admin\Filament\Resources\Accounts\Schemas\AccountForm;
use Modules\Admin\Filament\Resources\Accounts\Schemas\AccountInfolist;

use Modules\Admin\Filament\Resources\Accounts\Tables\AccountsTable;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Account';

    public static function form(Schema $schema): Schema
    {
        return AccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountsTable::configure($table);
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
    ->withCount('children');
    
}


    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'view' => ViewAccount::route('/{record}'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }
}
