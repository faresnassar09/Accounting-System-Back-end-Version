<?php

namespace Modules\Admin\Filament\Resources\Budgets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Accounting\Models\Account;
use Modules\Branch\Models\Branch;

class BudgetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Budget Allocation Details')
                    ->description('Set fiscal year targets for operational and capital accounts.')
                    ->schema([
                        TextInput::make('fiscal_year')
                            ->label('Fiscal Year')
                            ->numeric()
                            ->default((int) now()->format('Y'))
                            ->minValue(2020)
                            ->maxValue(2050)
                            ->required(),

                        Select::make('account_id')
                            ->label('Budget Account')
                            ->options(function () {
                                return Account::query()
                                    ->orderBy('number')
                                    ->get()
                                    ->mapWithKeys(fn ($acc) => [
                                        $acc->id => "#{$acc->number} — {$acc->name}",
                                    ]);
                            })
                            ->searchable()
                            ->required(),

                        Select::make('branch_id')
                            ->label('Branch Dimension')
                            ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Consolidated / Corporate (All Branches)'),

                        TextInput::make('allocated_amount')
                            ->label('Allocated Budget ($)')
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->required()
                            ->prefix('$'),

                        Textarea::make('notes')
                            ->label('Assumptions & Notes')
                            ->placeholder('e.g., Annual cap based on Q4 projections')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
