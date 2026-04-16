<?php

namespace Modules\Admin\Filament\Resources\AccountingSettnings\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\AccountingSettnings\AccountingSettningsResource;

class EditAccountingSettnings extends EditRecord
{
    protected static string $resource = AccountingSettningsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
