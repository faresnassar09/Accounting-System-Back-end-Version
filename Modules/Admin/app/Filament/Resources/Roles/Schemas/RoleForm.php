<?php

namespace Modules\Admin\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                
                TextInput::make('lable'),
                Select::make('guard_name')
                ->label("group (for who ?)")
                ->options([ 'web' => 'users' , 'admin' => 'admins'])
                ->required(),
            ]);
    }
}
