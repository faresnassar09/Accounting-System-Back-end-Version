<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\SystemAdmin\Filament\Resources\Tenants\TenantResource;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

}
