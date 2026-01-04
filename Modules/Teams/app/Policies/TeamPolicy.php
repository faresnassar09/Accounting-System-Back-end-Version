<?php

namespace Modules\Teams\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Admin\Models\Admin;
use Modules\Teams\Models\Team;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}


    public function viewAny(Admin $admin){


        return $admin->hasRole('super_admin');

    }

    public function create(Admin $admin)
    {
        return $admin->hasRole('super_admin');
    }


    public function view(Admin $admin, Team $team)
    {
        return $admin->hasRole('super_admin')  && $admin->company_id === $team->company_id;
    }


    public function update(Admin $admin, Team $team)
{
    return  $admin->hasRole('super_admin') &&  $admin->company_id === $team->company_id;
}
public function delete(Admin $admin, Team $team)
{
    return$admin->hasRole('super_admin') && $admin->company_id === $team->company_id;
}




}
