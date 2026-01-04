<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                TextInput::make('organization_name'),
                TextInput::make('organization_industry'),
                TextInput::make('organization_address'),
                 

                Repeater::make('domains')
                ->relationship()
                ->schema([

                    TextInput::make('domain'),
                ]),


            ]);
    }
}
