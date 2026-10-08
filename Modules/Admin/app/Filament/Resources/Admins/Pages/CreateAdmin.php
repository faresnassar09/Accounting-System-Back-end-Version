<?php

namespace Modules\Admin\Filament\Resources\Admins\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Admins\AdminResource;
use Spatie\Permission\Models\Role;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
{
    $role  = Role::find($this->data['role']);

    if($role){
            $this->record->assignRole($role);

    }

}

}
