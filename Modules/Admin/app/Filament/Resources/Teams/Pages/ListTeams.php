<?php

namespace Modules\Admin\Filament\Resources\Teams\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\Teams\TeamResource;

class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
