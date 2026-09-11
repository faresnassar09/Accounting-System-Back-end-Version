<?php

namespace Modules\Admin\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('Client ID')
                    ->disabled()
                    ->copyable()
                    ->hiddenOn('create'),

                TextInput::make('name')
                    ->label('Client Name')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->placeholder('e.g. Accounting Integration, External API, Mobile App'),

                CheckboxList::make('grant_types')
                    ->label('Allowed Grant Types')
                    ->options([
                        'client_credentials' => 'Client Credentials (Machine-to-Machine API access)',
                        'password' => 'Password (User login via API)',
                        'authorization_code' => 'Authorization Code (Web applications with redirect)',
                        'personal_access' => 'Personal Access Tokens',
                    ])
                    ->default(['client_credentials'])
                    ->required(),

                TagsInput::make('redirect_uris')
                    ->label('Redirect URIs')
                    ->placeholder('Add URI and press Enter')
                    ->helperText('Required for Authorization Code grant. Leave empty for Client Credentials.'),

                TextInput::make('secret')
                    ->label('Client Secret')
                    ->default(fn () => Str::random(40))
                    ->password()
                    ->revealable()
                    ->copyable()
                    ->helperText('API secret used for token generation. You can copy or reveal it.')
                    ->readOnlyOn('edit'),

                Toggle::make('revoked')
                    ->label('Revoked')
                    ->helperText('Revoking this client immediately disables all API tokens issued by it.')
                    ->default(false)
                    ->hiddenOn('create'),
            ]);
    }
}
