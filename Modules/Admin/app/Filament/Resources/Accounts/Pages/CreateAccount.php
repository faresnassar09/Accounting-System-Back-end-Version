<?php

namespace Modules\Admin\Filament\Resources\Accounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Accounts\AccountResource;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;
 
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
