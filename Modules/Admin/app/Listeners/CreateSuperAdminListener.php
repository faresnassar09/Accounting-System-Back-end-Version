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

$superAdminRole = Role::firstOrCreate(
        [
            'name' => 'super_admin',
            'guard_name' => 'admin',
        ],
        [
            'lable' => 'Super Admin',
        ]
    );

    $adminRole = Role::firstOrCreate(
        [
            'name' => 'admin',
            'guard_name' => 'admin',
        ],
        [
            'lable' => 'Admin', 
        ]
    );

    $admin = Admin::firstOrCreate(
        [
            'email' => 'admin@superadmin.com',
        ],
        [
            'name' => 'Super Admin',
            'password' => Hash::make('00000000'),
        ]
    );

    $admin->assignRole($superAdminRole);

    }
}
