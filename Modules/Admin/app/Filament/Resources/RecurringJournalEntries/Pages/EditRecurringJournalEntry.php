<?php

namespace Modules\Admin\Filament\Resources\RecurringJournalEntries\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\RecurringJournalEntryResource;

class EditRecurringJournalEntry extends EditRecord
{
    protected static string $resource = RecurringJournalEntryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $lines = collect($data['lines'] ?? []);
        $data['total_debit'] = (float) $lines->sum('debit');
        $data['total_credit'] = (float) $lines->sum('credit');

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
