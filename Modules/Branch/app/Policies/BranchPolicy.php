<?php

namespace Modules\Branch\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Admin\Models\Admin;
use Modules\Branch\Models\Branch;

class BranchPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}

    public function viewAny(Admin $admin){

        return current_guard_user()->hasRole('super_admin');

    }

    public function view(Admin $admin, Branch $branch){

        return current_guard_user()
         && current_guard_user()->hasRole('super_admin')
         && $admin->company_id === $branch->company_id;

    }

    public function create(Admin $admin){

        return current_guard_user()
        && current_guard_user()->hasRole('super_admin');
    }
    
    public function update(Admin $admin, Branch $branch){

        return current_guard_user()
        && current_guard_user()->hasRole('super_admin')
        && $admin->company_id === $branch->company_id;    
    }
    
    public function delete(Admin $admin, Branch $branch){

        return current_guard_user()
        && current_guard_user()->hasRole('super_admin')
        && $admin->company_id === $branch->company_id;
    }


}
