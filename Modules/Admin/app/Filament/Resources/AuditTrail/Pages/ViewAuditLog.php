<?php

namespace Modules\Admin\Filament\Resources\AuditTrail\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Admin\Filament\Resources\AuditTrail\AuditTrailResource;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditTrailResource::class;
}
