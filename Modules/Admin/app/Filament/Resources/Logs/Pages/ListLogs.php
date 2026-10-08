<?php

namespace Modules\Admin\Filament\Resources\Logs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\Logs\LogResource;

class ListLogs extends ListRecords
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

}
