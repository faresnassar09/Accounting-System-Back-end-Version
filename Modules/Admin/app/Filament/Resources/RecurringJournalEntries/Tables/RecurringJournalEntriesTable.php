<?php

namespace Modules\Admin\Filament\Resources\RecurringJournalEntries\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Accounting\Models\RecurringJournalEntry;
use Modules\Accounting\Services\CoreAccounting\RecurringJournalEntryService;
use Modules\Branch\Models\Branch;

class RecurringJournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_template')
                    ->label('Reference Pattern')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('frequency')
                    ->label('Frequency')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily'     => 'gray',
                        'weekly'    => 'info',
                        'monthly'   => 'primary',
                        'quarterly' => 'warning',
                        'yearly'    => 'purple',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'paused'    => 'warning',
                        'completed' => 'gray',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('next_run_date')
                    ->label('Next Run')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('last_run_date')
                    ->label('Last Run')
                    ->date('Y-m-d')
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_debit')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('currency_code')
                    ->label('Currency')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->default('Consolidated')
                    ->badge()
                    ->color(fn ($state) => $state === 'Consolidated' ? 'gray' : 'info')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('next_run_date', 'asc')
            ->filters([
                SelectFilter::make('frequency')
                    ->options([
                        'daily'     => 'Daily',
                        'weekly'    => 'Weekly',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly'    => 'Yearly',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'paused'    => 'Paused',
                        'completed' => 'Completed',
                    ]),
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([
                Action::make('run_now')
                    ->label('Run Now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Post Next Recurring Entry Now')
                    ->modalDescription('Generate and post the next balanced journal entry immediately according to this schedule template?')
                    ->action(function (RecurringJournalEntry $record) {
                        try {
                            $service = app(RecurringJournalEntryService::class);
                            $entry = $service->postEntry($record, now(), auth('admin')->id());
                            Notification::make()
                                ->title('Entry Posted Successfully')
                                ->body("Created journal entry #{$entry->reference} for \${$entry->total_debit}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Posting Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('toggle_status')
                    ->label(fn (RecurringJournalEntry $record) => $record->status === 'active' ? 'Pause' : 'Resume')
                    ->icon(fn (RecurringJournalEntry $record) => $record->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-arrow-path')
                    ->color(fn (RecurringJournalEntry $record) => $record->status === 'active' ? 'warning' : 'primary')
                    ->hidden(fn (RecurringJournalEntry $record) => $record->status === 'completed')
                    ->action(function (RecurringJournalEntry $record) {
                        $service = app(RecurringJournalEntryService::class);
                        $service->toggleStatus($record);
                        Notification::make()
                            ->title('Schedule status updated to ' . ucfirst($record->status))
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
