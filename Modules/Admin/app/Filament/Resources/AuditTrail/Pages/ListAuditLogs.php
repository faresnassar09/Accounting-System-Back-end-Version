<?php

namespace Modules\Admin\Filament\Resources\AuditTrail\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\AuditTrail\AuditTrailResource;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditTrailResource::class;
}
