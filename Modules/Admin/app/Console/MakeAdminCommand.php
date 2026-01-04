<?php

namespace Modules\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Admin\Models\Admin ;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:admin';
  
    protected $description = 'Create New Filament Admin.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

   
    public function handle() {

        $copany_id = $this->ask('Company Id');
        $branch_id = $this->ask('Branch Id');
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->ask('Password');

        $input = [
            'name' => $name,
            'email' => $email,
        ];

        $rules = [
            'name' => 'required|string|min:5',
            'email' => 'required|email|unique:admins,email',
        ];  


        $validator = Validator::make($input, $rules);


        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE; 
        }


        Admin::create([

            'name' => $name,
            'email' =>$email,
            'password' => $password,
            'company_id' => $copany_id,
            'branch_id' => $branch_id,
            
        ]);



        echo 'Admin Account Has created Successfully';


    }


    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
