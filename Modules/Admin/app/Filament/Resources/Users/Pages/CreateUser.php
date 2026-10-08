<?php

namespace Modules\Admin\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Users\UserResource;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static bool $canCreateAnother = false;

   

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}

protected function afterCreate(){

$role  = Role::find($this->data['role']);

$this->record->assignRole($role);

}
}
