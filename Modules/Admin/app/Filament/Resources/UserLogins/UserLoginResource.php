<?php

namespace Modules\Admin\Filament\Resources\UserLogins;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\UserLogins\Pages\ListUserLogins;
use Modules\Admin\Filament\Resources\UserLogins\Pages\ViewUserLogin;
use Modules\Admin\Filament\Resources\UserLogins\Schemas\UserLoginInfolist;
use Modules\Admin\Filament\Resources\UserLogins\Tables\UserLoginsTable;
use Modules\Authorization\Models\Passport\UserToken;

class UserLoginResource extends Resource
{
    protected static ?string $model = UserToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'User Logins';

    protected static ?string $modelLabel = 'User Login';

    protected static ?string $pluralModelLabel = 'User Logins';

    protected static ?int $navigationSort = 21;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserLoginInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserLoginsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user.branch', 'user.team', 'client']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserLogins::route('/'),
            'view' => ViewUserLogin::route('/{record}'),
        ];
    }
}
