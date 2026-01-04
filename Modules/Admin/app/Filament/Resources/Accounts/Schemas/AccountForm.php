<?php

namespace Modules\Admin\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Enums\AccountGroup;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                ->rules(['required','min:4','max:50']),

                TextInput::make('number')
                ->rules(['required','min:1','max:255','unique:accounts,number']),
            
            TextInput::make('description'),
                
            Select::make('parent_id')
                ->label('Belongs To Account')
                ->options(Account::query()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(function(string $state,Set $set){

                    $set('sub_classification', $state);
                }),

                
                Select::make('account_type_id')
                ->label('Sub Classification')
                ->visible(function(Get $get){
                    
                    $id = $get('parent_id');

                        $mainAccount =app(AccountRepositoryInterface::class)
                        ->findRootAccount($id);

                        return $mainAccount?->name === 'expenses';
         
                })
                ->options(AccountType::
                where('account_group',AccountGroup::EXPENSES->value)
                ->pluck('type','id')),
                
                Select::make('account_type_id')
                ->label('Sub Classification')
                ->visible(function(Get $get){
                    
                    $id = $get('parent_id');

                        $mainAccount =app(AccountRepositoryInterface::class)
                        ->findRootAccount($id);

                        return $mainAccount?->name === 'revenues';
         
                })
                ->options(AccountType::
                where('account_group',AccountGroup::REVENUES->value)
                ->pluck('type','id')),


                Select::make('account_type_id')
                ->label('Sub Classification')
                ->visible(function(Get $get){
                    
                    $id = $get('parent_id');

                        $mainAccount =app(AccountRepositoryInterface::class)
                        ->findRootAccount($id);

                        return $mainAccount?->name === 'assets';
         
                })
                ->options(AccountType::
                where('account_group',AccountGroup::ASSETS->value)
                ->pluck('type','id')),

                Select::make('account_type_id')
                ->label('Sub Classification')
                ->visible(function(Get $get){
                    
                    $id = $get('parent_id');

                        $mainAccount =app(AccountRepositoryInterface::class)
                        ->findRootAccount($id);

                        return $mainAccount?->name === 'liabilities';
         
                })
                ->options(AccountType::
                where('account_group',AccountGroup::LIABILITIES->value)
                ->pluck('type','id')),


                Select::make('account_type_id')
                ->label('Sub Classification')
                ->visible(function(Get $get){
                    
                    $id = $get('parent_id');

                        $mainAccount = app(AccountRepositoryInterface::class)
                        ->findRootAccount($id);

                        return $mainAccount?->name === 'equity';
         
                })->options(AccountType::
                where('account_group',AccountGroup::EQUITY->value)
                ->pluck('type','id')),


            ]);
    }
}
