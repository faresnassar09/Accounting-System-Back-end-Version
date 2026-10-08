<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\Currency;
use Modules\Branch\Models\Branch;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Journal Entry Details')
                    ->description('Specify header information, fiscal period date, multi-currency valuation, and organizational branch.')
                    ->schema([
                        TextInput::make('reference')
                            ->label('Reference #')
                            ->default(fn () => 'JV-' . strtoupper(Str::random(8)))
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'journal_entries', column: 'reference', ignoreRecord: true),

                        DateTimePicker::make('date')
                            ->label('Posting Date & Time')
                            ->default(now())
                            ->required(),

                        Select::make('branch_id')
                            ->label('Branch Location')
                            ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Consolidated / Corporate (No Branch)'),

                        Select::make('currency_code')
                            ->label('Transaction Currency')
                            ->options(function () {
                                return Currency::query()
                                    ->where('is_active', true)
                                    ->orderBy('is_base', 'desc')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [$c->code => "{$c->code} ({$c->symbol}) — {$c->name}"]);
                            })
                            ->default(fn () => Currency::getBaseCurrencyCode() ?? 'USD')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $currency = Currency::where('code', $state)->first();
                                if ($currency) {
                                    $set('exchange_rate', (float) $currency->exchange_rate);
                                }
                            })
                            ->required(),

                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate (to Base)')
                            ->numeric()
                            ->step(0.000001)
                            ->default(1.000000)
                            ->required()
                            ->minValue(0.000001)
                            ->helperText('1 unit of currency = X units of base ledger currency'),

                        TextInput::make('description')
                            ->label('Narration / Description')
                            ->placeholder('e.g., Monthly office supplies expense adjustment')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Journal Entry Lines (Double-Entry Bookkeeping)')
                    ->description('Each entry must contain at least 2 lines, and Total Debits must exactly equal Total Credits.')
                    ->schema([
                        Repeater::make('lines')
                            ->label('Line Items')
                            ->schema([
                                Select::make('account_id')
                                    ->label('Account')
                                    ->options(function () {
                                        return Account::query()
                                            ->orderBy('number')
                                            ->get()
                                            ->mapWithKeys(fn ($acc) => [
                                                $acc->id => "#{$acc->number} — {$acc->name}",
                                            ]);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(4),

                                Select::make('branch_id')
                                    ->label('Line Branch')
                                    ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->columnSpan(2),

                                TextInput::make('debit')
                                    ->label('Debit ($)')
                                    ->numeric()
                                    ->default(0.00)
                                    ->required()
                                    ->minValue(0)
                                    ->columnSpan(2),

                                TextInput::make('credit')
                                    ->label('Credit ($)')
                                    ->numeric()
                                    ->default(0.00)
                                    ->required()
                                    ->minValue(0)
                                    ->columnSpan(2),

                                TextInput::make('description')
                                    ->label('Line Memo / Details')
                                    ->nullable()
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->minItems(2)
                            ->defaultItems(2)
                            ->addActionLabel('+ Add Journal Line')
                            ->collapsible()
                            ->reorderableWithButtons(),
                    ]),
            ]);
    }
}
