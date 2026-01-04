<?php

namespace Modules\Accounting\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounting\Models\Account;
use Modules\Admin\Models\Admin;

class ChartAccountingPolicy
{
    use HandlesAuthorization;

    public function __construct() {}


    public function update(Admin $admin , Account $account){

       return  $admin->hasRole('super_admin');
    }

    public function delete(Admin $admin , Account $account){

        return  $admin->hasRole('super_admin'); 
        
    }


}
