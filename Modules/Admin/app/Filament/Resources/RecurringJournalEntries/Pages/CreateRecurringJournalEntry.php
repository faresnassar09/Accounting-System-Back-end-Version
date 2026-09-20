<?php

namespace Modules\Admin\Filament\Resources\RecurringJournalEntries\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\RecurringJournalEntryResource;

class CreateRecurringJournalEntry extends CreateRecord
{
    protected static string $resource = RecurringJournalEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth('admin')->id();

        if (empty($data['next_run_date'])) {
            $data['next_run_date'] = $data['start_date'] ?? now()->toDateString();
        }

        $lines = collect($data['lines'] ?? []);
        $data['total_debit'] = (float) $lines->sum('debit');
        $data['total_credit'] = (float) $lines->sum('credit');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
