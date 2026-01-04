<?php

namespace Modules\Admin\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Admin\Models\Admin;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}

    public function viewAny(Admin $admin){ 
          
       return $admin->hasRole('super_admin');
    }

    public function create(Admin $admin){

        return $admin->hasRole('super_admin');


    }
    public function update(Admin $admin){

        return $admin->hasRole('super_admin');

    }

    public function view(Admin $admin){

        return $admin->hasRole('super_admin');

    }
    
    public function delete(Admin $admin){

        return $admin->hasRole('super_admin');

    }
  


}
