<?php

namespace Modules\Admin\Filament\Resources\RecurringJournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\Currency;
use Modules\Branch\Models\Branch;

class RecurringJournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Recurring Schedule Configuration')
                    ->description('Set frequency, recurrence bounds, reference pattern, and execution status.')
                    ->schema([
                        TextInput::make('reference_template')
                            ->label('Reference Pattern')
                            ->placeholder('e.g., REC-RENT-{YYYY}-{MM}')
                            ->default('REC-ENTRY-{YYYY}-{MM}')
                            ->helperText('Placeholders: {YYYY}, {MM}, {DD}, {SEQ}')
                            ->required()
                            ->maxLength(100),

                        Select::make('frequency')
                            ->label('Recurrence Frequency')
                            ->options([
                                'daily'     => 'Daily',
                                'weekly'    => 'Weekly',
                                'monthly'   => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'yearly'    => 'Yearly',
                            ])
                            ->default('monthly')
                            ->required(),

                        Select::make('status')
                            ->label('Schedule Status')
                            ->options([
                                'active'    => 'Active',
                                'paused'    => 'Paused',
                                'completed' => 'Completed',
                            ])
                            ->default('active')
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now()->toDateString())
                            ->required(),

                        DatePicker::make('next_run_date')
                            ->label('Next Run Date')
                            ->default(now()->toDateString())
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('End / Expiry Date (Optional)')
                            ->nullable(),

                        Select::make('branch_id')
                            ->label('Branch Dimension')
                            ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Consolidated (No Branch)'),

                        Select::make('currency_code')
                            ->label('Currency')
                            ->options(fn () => Currency::query()->where('is_active', true)->orderBy('is_base', 'desc')->get()->mapWithKeys(fn ($c) => [$c->code => "{$c->code} ({$c->symbol}) — {$c->name}"]))
                            ->default(fn () => Currency::getBaseCurrencyCode() ?? 'USD')
                            ->required(),

                        Toggle::make('auto_post')
                            ->label('Auto-Post as Approved')
                            ->helperText('Approved directly into ledger vs created as Draft')
                            ->default(true),

                        TextInput::make('description')
                            ->label('Schedule Narration / Description')
                            ->placeholder('e.g., Monthly office building lease payment and amortization')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Template Lines (Double-Entry Bookkeeping)')
                    ->description('Define recurring debit and credit lines. Sum of debits must equal sum of credits.')
                    ->schema([
                        Repeater::make('lines')
                            ->relationship('lines')
                            ->label('Scheduled Line Items')
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
                                    ->minValue(0)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('credit')
                                    ->label('Credit ($)')
                                    ->numeric()
                                    ->default(0.00)
                                    ->minValue(0)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('description')
                                    ->label('Line Memo')
                                    ->nullable()
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->minItems(2)
                            ->defaultItems(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
