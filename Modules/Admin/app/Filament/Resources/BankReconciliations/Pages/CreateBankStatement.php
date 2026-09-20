<?php

namespace Modules\Admin\Filament\Resources\BankReconciliations\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\BankReconciliations\BankStatementResource;

class CreateBankStatement extends CreateRecord
{
    protected static string $resource = BankStatementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
