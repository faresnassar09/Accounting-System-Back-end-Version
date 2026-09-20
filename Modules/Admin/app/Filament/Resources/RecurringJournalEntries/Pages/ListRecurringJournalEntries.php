<?php

namespace Modules\Admin\Filament\Resources\RecurringJournalEntries\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\RecurringJournalEntryResource;

class ListRecurringJournalEntries extends ListRecords
{
    protected static string $resource = RecurringJournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Recurring Schedule')
                ->icon('heroicon-o-plus'),
        ];
    }
}
