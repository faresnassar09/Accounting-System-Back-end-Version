<?php

namespace Modules\Admin\Filament\Resources\FixedAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Modules\Accounting\Models\Account;
use Modules\Branch\Models\Branch;

class FixedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Specification & Classification')
                    ->description('Record acquisition parameters, asset tag identifier, and operating category.')
                    ->schema([
                        TextInput::make('asset_number')
                            ->label('Asset Tag / Identifier')
                            ->default(fn () => 'FA-' . strtoupper(Str::random(6)))
                            ->required()
                            ->maxLength(50)
                            ->unique(table: 'fixed_assets', column: 'asset_number', ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Asset Name / Description')
                            ->placeholder('e.g., Heavy Duty Forklift / Delivery Van')
                            ->required()
                            ->maxLength(150),

                        Select::make('category')
                            ->label('Asset Class')
                            ->options([
                                'equipment'  => 'Machinery & Equipment',
                                'vehicles'   => 'Motor Vehicles & Fleet',
                                'furniture'  => 'Fixtures & Office Furniture',
                                'buildings'  => 'Buildings & Leasehold Improvements',
                                'land'       => 'Land & Property',
                                'intangible' => 'Intangible Assets & Patents',
                            ])
                            ->default('equipment')
                            ->required(),

                        Select::make('branch_id')
                            ->label('Assigned Branch')
                            ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Consolidated (Corporate Asset)'),
                    ])
                    ->columns(2),

                Section::make('Capitalization & Depreciation Parameters')
                    ->description('Capitalization cost, salvage expectations, and useful amortization lifespan.')
                    ->schema([
                        DatePicker::make('purchase_date')
                            ->label('Acquisition Date')
                            ->default(now()->toDateString())
                            ->required(),

                        TextInput::make('purchase_cost')
                            ->label('Original Purchase Cost')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->required(),

                        TextInput::make('salvage_value')
                            ->label('Residual / Salvage Value')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00)
                            ->minValue(0),

                        TextInput::make('useful_life_months')
                            ->label('Useful Life')
                            ->numeric()
                            ->suffix('Months')
                            ->default(60)
                            ->minValue(1)
                            ->required(),

                        Select::make('depreciation_method')
                            ->label('Depreciation Schedule Method')
                            ->options([
                                'straight_line'     => 'Straight-Line (Equal Monthly Distribution)',
                                'declining_balance' => 'Double-Declining Balance (Accelerated)',
                            ])
                            ->default('straight_line')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('General Ledger Account Mappings')
                    ->description('Required GL balance sheet and income statement posting accounts.')
                    ->schema([
                        Select::make('asset_account_id')
                            ->label('Fixed Asset Cost Account')
                            ->options(fn () => Account::query()->orderBy('number')->get()->mapWithKeys(fn ($acc) => [$acc->id => "#{$acc->number} — {$acc->name}"]))
                            ->searchable()
                            ->required(),

                        Select::make('depreciation_expense_account_id')
                            ->label('Depreciation Expense Account (P&L)')
                            ->options(fn () => Account::query()->orderBy('number')->get()->mapWithKeys(fn ($acc) => [$acc->id => "#{$acc->number} — {$acc->name}"]))
                            ->searchable()
                            ->required(),

                        Select::make('accumulated_depreciation_account_id')
                            ->label('Accumulated Depreciation Account (Contra Asset)')
                            ->options(fn () => Account::query()->orderBy('number')->get()->mapWithKeys(fn ($acc) => [$acc->id => "#{$acc->number} — {$acc->name}"]))
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }
}
