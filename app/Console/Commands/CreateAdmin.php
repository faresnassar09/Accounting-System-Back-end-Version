<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\Models\Admin;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $name = $this->ask('name');
        $email = $this->ask('email');
        $password = $this->ask('password');
        $branchId = $this->ask('branch');

        Admin::create([
            
            'branch_id' => $branchId,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
