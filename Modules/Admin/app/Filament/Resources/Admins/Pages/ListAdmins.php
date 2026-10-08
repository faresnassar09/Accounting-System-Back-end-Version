<?php

namespace Modules\Admin\Filament\Resources\Admins\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\Admins\AdminResource;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
