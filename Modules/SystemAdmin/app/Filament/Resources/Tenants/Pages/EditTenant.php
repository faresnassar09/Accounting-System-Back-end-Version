<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\SystemAdmin\Filament\Resources\Tenants\TenantResource;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
