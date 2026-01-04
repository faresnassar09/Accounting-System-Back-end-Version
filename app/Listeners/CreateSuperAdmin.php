<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Role;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Events\DatabaseMigrated;

class CreateSuperAdmin
{

  
    public function handle(DatabaseMigrated $event): void
    {
        tenancy()->initialize($event->tenant);


       $superAdminRole =  Role::create([
        'name' => 'super_admin',
        'lable' => 'Super Admin',
        'guard_name' => 'admin',
    
    ]);

      $admin =  Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@'.'super_admin'.'.com',
            'password' => Hash::make('password123'),
            'is_super_admin' => true,
        ]);

        $admin->assignRole($superAdminRole);

    }
}
