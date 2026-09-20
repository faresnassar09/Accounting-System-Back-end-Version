<?php

namespace Modules\Admin\Filament\Resources\FixedAssets\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\FixedAsset;
use Modules\Accounting\Services\CoreAccounting\FixedAssetService;

class FixedAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_number')
                    ->label('Asset #')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Asset Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Class')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('purchase_date')
                    ->label('Acquired')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('purchase_cost')
                    ->label('Acquisition Cost')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('accumulated_depreciation')
                    ->label('Accumulated Depr.')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('book_value')
                    ->label('Net Book Value')
                    ->money('USD')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'            => 'success',
                        'fully_depreciated' => 'info',
                        'disposed'          => 'danger',
                        default             => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('purchase_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'equipment'  => 'Machinery & Equipment',
                        'vehicles'   => 'Vehicles & Fleet',
                        'furniture'  => 'Furniture & Fixtures',
                        'buildings'  => 'Buildings',
                        'land'       => 'Land',
                        'intangible' => 'Intangibles',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'active'            => 'Active',
                        'fully_depreciated' => 'Fully Depreciated',
                        'disposed'          => 'Disposed',
                    ]),
            ])
            ->recordActions([
                Action::make('depreciate')
                    ->label('Depreciate')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->hidden(fn (FixedAsset $record) => $record->status !== 'active')
                    ->form([
                        DatePicker::make('period_date')
                            ->label('Depreciation Period Date')
                            ->default(now()->startOfMonth()->toDateString())
                            ->required(),
                    ])
                    ->action(function (FixedAsset $record, array $data) {
                        try {
                            $service = app(FixedAssetService::class);
                            $entry = $service->postMonthlyDepreciation(
                                $record,
                                Carbon::parse($data['period_date']),
                                auth('admin')->id()
                            );
                            Notification::make()
                                ->title('Depreciation Successfully Posted')
                                ->body("Recorded \${$entry->total_debit} via JV #{$entry->reference}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Depreciation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('dispose')
                    ->label('Dispose / Retire')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->hidden(fn (FixedAsset $record) => $record->status === 'disposed')
                    ->form([
                        TextInput::make('disposal_amount')
                            ->label('Proceeds Received ($)')
                            ->numeric()
                            ->default(0.00)
                            ->minValue(0)
                            ->required(),

                        DatePicker::make('disposal_date')
                            ->label('Disposal Date')
                            ->default(now()->toDateString())
                            ->required(),

                        Select::make('cash_account_id')
                            ->label('Deposit / Proceeds Account (Bank or Cash)')
                            ->options(fn () => Account::query()->orderBy('number')->get()->mapWithKeys(fn ($acc) => [$acc->id => "#{$acc->number} — {$acc->name}"]))
                            ->searchable()
                            ->required(),

                        Select::make('gain_loss_account_id')
                            ->label('Gain / Loss on Disposal Account (P&L)')
                            ->options(fn () => Account::query()->orderBy('number')->get()->mapWithKeys(fn ($acc) => [$acc->id => "#{$acc->number} — {$acc->name}"]))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (FixedAsset $record, array $data) {
                        try {
                            $service = app(FixedAssetService::class);
                            $entry = $service->disposeAsset(
                                $record,
                                (float) $data['disposal_amount'],
                                Carbon::parse($data['disposal_date']),
                                (int) $data['cash_account_id'],
                                (int) $data['gain_loss_account_id'],
                                auth('admin')->id()
                            );
                            Notification::make()
                                ->title('Asset Disposed Successfully')
                                ->body("Recorded asset retirement via JV #{$entry->reference}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Disposal Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
