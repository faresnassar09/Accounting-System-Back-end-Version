<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\JournalEntries\JournalEntryResource;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Journal Entry')
                ->icon('heroicon-o-plus'),
        ];
    }
}
