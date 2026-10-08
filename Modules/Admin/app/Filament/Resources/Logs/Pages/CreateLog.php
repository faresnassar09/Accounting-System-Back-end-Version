<?php

namespace Modules\Admin\Filament\Resources\Logs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Logs\LogResource;

class CreateLog extends CreateRecord
{
    protected static string $resource = LogResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
