<?php

namespace Modules\Admin\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                ->rules(['required','min:4','max:255']),

                TextInput::make('address')
                ->rules(['required','max:255']),
                TextInput::make('phone')
                ->rules([
                    'required','max:20','min:4',
                ]),

                TextInput::make('code')
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule,$livewire) => $rule->where('company_id',current_guard_user()->company_id)),
            ]);
    }
}
