<?php

namespace Modules\Admin\Filament\Resources\Clients;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\Clients\Pages\CreateClient;
use Modules\Admin\Filament\Resources\Clients\Pages\EditClient;
use Modules\Admin\Filament\Resources\Clients\Pages\ListClients;
use Modules\Admin\Filament\Resources\Clients\Pages\ViewClient;
use Modules\Admin\Filament\Resources\Clients\Schemas\ClientForm;
use Modules\Admin\Filament\Resources\Clients\Schemas\ClientInfolist;
use Modules\Admin\Filament\Resources\Clients\Tables\ClientsTable;
use Modules\Authorization\Models\Passport\Client;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'OAuth Clients';

    protected static ?string $modelLabel = 'OAuth Client';

    protected static ?string $pluralModelLabel = 'OAuth Clients';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
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
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
