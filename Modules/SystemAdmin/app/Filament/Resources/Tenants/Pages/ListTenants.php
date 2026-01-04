<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\SystemAdmin\Filament\Resources\Tenants\TenantResource;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
