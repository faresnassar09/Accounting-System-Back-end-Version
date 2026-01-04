<?php

namespace Modules\Admin\Filament\Resources\Branches\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Admin\Filament\Resources\Branches\BranchResource;

class ViewBranch extends ViewRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
