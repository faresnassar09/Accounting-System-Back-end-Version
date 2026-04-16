<?php

namespace Modules\Admin\Filament\Resources\AccountingSettnings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountingSettningsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('account_id')
                    ->relationship('account', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('label'),

                TextInput::make('description')
                    ->disabled(),
            ]);
    }
}
