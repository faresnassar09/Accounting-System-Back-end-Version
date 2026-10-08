<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;
use Modules\Admin\Filament\Resources\JournalEntries\JournalEntryResource;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reverse')
                ->label('Reverse / Void Entry')
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
        ];
    }
}
