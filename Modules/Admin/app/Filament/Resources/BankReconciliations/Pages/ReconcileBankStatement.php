<?php

namespace Modules\Admin\Filament\Resources\BankReconciliations\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\BankReconciliations\BankStatementResource;

class ReconcileBankStatement extends EditRecord
{
    protected static string $resource = BankStatementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
