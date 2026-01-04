<?php

namespace Modules\Authorization\Observers;

use Illuminate\Support\Str;
use Modules\Authorization\Models\Role;

class RoleObserver
{

public function creating(Role $role){

    $role->name = Str::slug($role->lable,'_');
}
}
