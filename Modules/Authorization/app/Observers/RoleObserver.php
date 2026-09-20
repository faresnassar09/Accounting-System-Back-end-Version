<?php

namespace Modules\Authorization\Observers;

use Illuminate\Support\Str;
use Modules\Authorization\Models\Role;

class RoleObserver
{
    public function creating(Role $role)
    {
        if (empty($role->name) && !empty($role->lable)) {
            $role->name = Str::slug($role->lable, '_');
        } elseif (empty($role->lable) && !empty($role->name)) {
            $role->lable = Str::headline($role->name);
        }
    }
}
