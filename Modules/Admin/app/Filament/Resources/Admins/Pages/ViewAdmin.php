<?php

namespace Modules\Admin\Filament\Resources\Admins\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Admin\Filament\Resources\Admins\AdminResource;

class ViewAdmin extends ViewRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
