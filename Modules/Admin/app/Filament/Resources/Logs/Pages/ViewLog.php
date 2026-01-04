<?php

namespace Modules\Admin\Filament\Resources\Logs\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Infolists\Components\TextEntry;
use Modules\Admin\Filament\Resources\Logs\LogResource;

class ViewLog extends ViewRecord
{
    protected static string $resource = LogResource::class;


    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
