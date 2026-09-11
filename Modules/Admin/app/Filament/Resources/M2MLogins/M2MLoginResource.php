<?php

namespace Modules\Admin\Filament\Resources\M2MLogins;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\M2MLogins\Pages\ListM2MLogins;
use Modules\Admin\Filament\Resources\M2MLogins\Pages\ViewM2MLogin;
use Modules\Admin\Filament\Resources\M2MLogins\Schemas\M2MLoginInfolist;
use Modules\Admin\Filament\Resources\M2MLogins\Tables\M2MLoginsTable;
use Modules\Authorization\Models\Passport\ClientToken;

class M2MLoginResource extends Resource
{
    protected static ?string $model = ClientToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $navigationLabel = 'M2M Service Logins';

    protected static ?string $modelLabel = 'M2M Service Login';

    protected static ?string $pluralModelLabel = 'M2M Service Logins';

    protected static ?int $navigationSort = 22;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return M2MLoginInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return M2MLoginsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListM2MLogins::route('/'),
            'view' => ViewM2MLogin::route('/{record}'),
        ];
    }
}
