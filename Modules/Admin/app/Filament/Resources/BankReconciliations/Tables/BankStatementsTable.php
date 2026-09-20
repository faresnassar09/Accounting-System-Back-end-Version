<?php

namespace Modules\Admin\Filament\Resources\BankReconciliations\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Accounting\Models\BankStatement;
use Modules\Accounting\Services\CoreAccounting\BankReconciliationService;

class BankStatementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.name')
                    ->label('Bank Account')
                    ->formatStateUsing(fn ($record) => $record->account ? "#{$record->account->number} — {$record->account->name}" : '—')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('statement_date')
                    ->label('Statement Date')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('opening_balance')
                    ->label('Opening Balance')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('closing_balance')
                    ->label('Target Closing')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('calculated_balance')
                    ->label('Reconciled Total')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('discrepancy')
                    ->label('Discrepancy')
                    ->money('USD')
                    ->badge()
                    ->color(fn ($state) => abs((float) $state) < 0.01 ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'       => 'gray',
                        'reconciling' => 'warning',
                        'reconciled'  => 'success',
                        default       => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('statement_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'       => 'Draft',
                        'reconciling' => 'In Progress',
                        'reconciled'  => 'Reconciled',
                    ]),
            ])
            ->recordActions([
                Action::make('auto_match')
                    ->label('Auto-Match')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->hidden(fn (BankStatement $record) => $record->status === 'reconciled')
                    ->action(function (BankStatement $record) {
                        $service = app(BankReconciliationService::class);
                        $count = $service->autoMatch($record);
                        Notification::make()
                            ->title("Auto-Matched {$count} transactions")
                            ->success()
                            ->send();
                    }),

                Action::make('finalize')
                    ->label('Finalize')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Finalize Bank Reconciliation')
                    ->modalDescription('Lock this bank statement reconciliation? Discrepancy must equal zero.')
                    ->hidden(fn (BankStatement $record) => $record->status === 'reconciled')
                    ->action(function (BankStatement $record) {
                        try {
                            $service = app(BankReconciliationService::class);
                            $service->finalizeReconciliation($record);
                            Notification::make()
                                ->title('Bank Statement Successfully Reconciled')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Finalization Blocked')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (BankStatement $record) => $record->status === 'reconciled'),
            ]);
    }
}
