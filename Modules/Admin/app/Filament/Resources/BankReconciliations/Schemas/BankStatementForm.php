<?php

namespace Modules\Admin\Filament\Resources\BankReconciliations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Accounting\Models\Account;

class BankStatementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bank Statement Header')
                    ->description('Specify target cash/bank ledger account and statement cut-off balance.')
                    ->schema([
                        Select::make('account_id')
                            ->label('Bank / Cash Ledger Account')
                            ->options(function () {
                                return Account::query()
                                    ->orderBy('number')
                                    ->get()
                                    ->mapWithKeys(fn ($acc) => [$acc->id => "#{$acc->number} — {$acc->name}"]);
                            })
                            ->searchable()
                            ->required(),

                        DatePicker::make('statement_date')
                            ->label('Statement Closing Date')
                            ->default(now()->toDateString())
                            ->required(),

                        TextInput::make('opening_balance')
                            ->label('Statement Opening Balance')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00)
                            ->required(),

                        TextInput::make('closing_balance')
                            ->label('Statement Closing Balance (Target)')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Statement Line Items')
                    ->description('Bank feed transactions. Positive amounts represent deposits, negative amounts represent withdrawals.')
                    ->schema([
                        Repeater::make('lines')
                            ->relationship('lines')
                            ->label('Statement Transactions')
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Tx Date')
                                    ->default(now()->toDateString())
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('description')
                                    ->label('Description / Narration')
                                    ->placeholder('e.g. Wire Deposit / Merchant Fee')
                                    ->required()
                                    ->columnSpan(5),

                                TextInput::make('reference')
                                    ->label('Ref / Check #')
                                    ->nullable()
                                    ->columnSpan(2),

                                TextInput::make('amount')
                                    ->label('Amount (+/- $)')
                                    ->numeric()
                                    ->helperText('(-) Outflow / (+) Inflow')
                                    ->required()
                                    ->columnSpan(3),
                            ])
                            ->columns(12)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
