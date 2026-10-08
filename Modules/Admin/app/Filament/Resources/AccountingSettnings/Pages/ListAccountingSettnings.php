<?php

namespace Modules\Admin\Filament\Resources\AccountingSettnings\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\AccountingSettnings\AccountingSettningsResource;

class ListAccountingSettnings extends ListRecords
{
    protected static string $resource = AccountingSettningsResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
