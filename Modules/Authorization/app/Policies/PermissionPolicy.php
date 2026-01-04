<?php

namespace Modules\Authorization\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Permission;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}


    public function update(Admin $admin)
    {

        return $admin->hasRole('super_admin');
    }

    public function delete(Admin $admin)
    {

        return $admin->hasRole('super_admin');
    }

    public function viewAny(Admin $admin)
    {

        return $admin->hasRole('super_admin');
    }

    public function create(Admin $admin)
    {

        return $admin->hasRole('super_admin');
    }
}
