<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\SystemAdmin\Filament\Resources\Tenants\TenantResource;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
