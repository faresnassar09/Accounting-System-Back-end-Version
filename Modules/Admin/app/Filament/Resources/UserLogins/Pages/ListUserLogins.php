<?php

namespace Modules\Admin\Filament\Resources\UserLogins\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\UserLogins\UserLoginResource;

class ListUserLogins extends ListRecords
{
    protected static string $resource = UserLoginResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
