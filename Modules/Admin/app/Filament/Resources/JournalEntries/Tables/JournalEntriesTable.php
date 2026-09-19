<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'journal'    => 'info',
                        'opening'    => 'warning',
                        'closing'    => 'gray',
                        'adjustment' => 'purple',
                        default      => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'draft'    => 'warning',
                        'cancled'  => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('total_debit')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('total_credit')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('reverse')
                    ->label('Reverse')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reverse Journal Entry')
                    ->modalDescription('This will create an opposite adjustment journal entry swapping debits and credits, and mark this entry as cancelled.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason for Reversal')
                            ->placeholder('e.g., Incorrect amount or account')
                            ->required(),
                        DatePicker::make('reversal_date')
                            ->label('Reversal Date')
                            ->default(now()),
                    ])
                    ->visible(fn (JournalEntry $record): bool => $record->status !== 'cancled')
                    ->action(function (JournalEntry $record, array $data) {
                        try {
                            $service = app(JournalEntryService::class);
                            $service->reverse(
                                id: $record->id,
                                reason: $data['reason'] ?? null,
                                reversalDate: !empty($data['reversal_date']) ? Carbon::parse($data['reversal_date'])->format('Y-m-d H:i:s') : null,
                                userId: current_guard_user()?->id
                            );

                            Notification::make()
                                ->title('Journal Entry Reversed')
                                ->body("Entry #{$record->id} ({$record->reference}) has been reversed successfully.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Reversal Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
