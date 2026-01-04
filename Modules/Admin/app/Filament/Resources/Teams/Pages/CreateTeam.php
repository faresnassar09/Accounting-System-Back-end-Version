<?php

namespace Modules\Admin\Filament\Resources\Teams\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Teams\TeamResource;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected static bool $canCreateAnother = false;

   

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}


}
