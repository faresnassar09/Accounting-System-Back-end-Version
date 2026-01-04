<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Tenant;

class company extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:company';

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

        // $name = $this->ask('name');
        // $industry = $this->ask('industry');
        // $code = $this->ask('code');

       $tenant =  Tenant::create();
       $tenant->domains()->create(['domain' => 'fares']);


    }
}
