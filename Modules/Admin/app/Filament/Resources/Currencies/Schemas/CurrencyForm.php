<?php

namespace Modules\Admin\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Currency & Valuation Settings')
                    ->description('Define ISO currency codes, ledger exchange rates, and base anchor status.')
                    ->schema([
                        TextInput::make('code')
                            ->label('ISO Currency Code')
                            ->placeholder('USD, EUR, SAR, etc.')
                            ->length(3)
                            ->required()
                            ->unique(table: 'currencies', column: 'code', ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Currency Name')
                            ->placeholder('e.g., US Dollar, Euro')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('symbol')
                            ->label('Currency Symbol')
                            ->placeholder('$, €, £, ر.س')
                            ->required()
                            ->maxLength(10),

                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate (to Base)')
                            ->numeric()
                            ->step(0.000001)
                            ->default(1.000000)
                            ->required()
                            ->helperText('1 unit of this currency = X units of Base Currency (Base currency rate must equal 1.0)'),

                        Toggle::make('is_base')
                            ->label('Base Currency')
                            ->helperText('Anchor currency for all financial ledger statements.')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Enable for posting transactions and journal entries.')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
