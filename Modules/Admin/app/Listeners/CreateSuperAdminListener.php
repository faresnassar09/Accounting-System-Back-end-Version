<?php

namespace Modules\Admin\Listeners;

use Illuminate\Support\Facades\Hash;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Role;
use Stancl\Tenancy\Events\DatabaseMigrated;

class CreateSuperAdminListener
{


    public function handle(DatabaseMigrated $event): void
    {

       $superAdminRole =  Role::create([
        'name' => 'super_admin',
        'lable' => 'Super Admin',
        'guard_name' => 'admin',
    
    ]);

      $admin =  Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@'.'super_admin'.'.com',
            'password' => Hash::make('00000000'),
            'is_super_admin' => true,
        ]);

        $admin->assignRole($superAdminRole);

    }
}
