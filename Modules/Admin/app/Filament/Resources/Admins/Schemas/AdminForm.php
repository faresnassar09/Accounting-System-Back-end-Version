<?php

namespace Modules\Admin\Filament\Resources\Admins\Schemas;

use Dom\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Branch\Models\Branch;
use Spatie\Permission\Models\Role;
use Symfony\Contracts\Service\Attribute\Required;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                ->rules(['required','string','min:5','max:20'])
                ->required(),

                Select::make('branch_id')
                ->label('Branch')
                ->searchable()
                ->required()
                ->validationMessages([
                    'required' => 'Branch Filed is Require',
                ])
                ->options(Branch::query()->pluck('name','id')),

                TextInput::make('email')
                ->rules(['required','email','unique:admins,email'])
                ->required(),

                TextInput::make('password')
                ->rules(['required','min:8','max:20']),

                Select::make('role')
                ->label('assign Role *Admin Role is default')
                ->default('admin')
                ->dehydrated(false)
                ->options(Role::query()->where('guard_name','admin')->pluck('name','id')),
      

            ]);
    }
}
  