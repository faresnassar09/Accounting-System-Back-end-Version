<?php

namespace Modules\Admin\Filament\Resources\AccountingSettnings\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Admin\Filament\Resources\AccountingSettnings\AccountingSettningsResource;

class ViewAccountingSettnings extends ViewRecord
{
    protected static string $resource = AccountingSettningsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
