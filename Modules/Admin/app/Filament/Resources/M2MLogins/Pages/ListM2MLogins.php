<?php

namespace Modules\Admin\Filament\Resources\M2MLogins\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\M2MLogins\M2MLoginResource;

class ListM2MLogins extends ListRecords
{
    protected static string $resource = M2MLoginResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
