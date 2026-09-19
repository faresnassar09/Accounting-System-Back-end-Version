<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\JournalEntries\JournalEntryResource;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
