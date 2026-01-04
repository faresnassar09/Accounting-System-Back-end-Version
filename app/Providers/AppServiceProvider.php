<?php

namespace App\Providers;

use App\Listeners\CreateSuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportFileUploads\FilePreviewController;
use Modules\User\Policies\UserPolicy;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Events\DatabaseMigrated;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class AppServiceProvider extends ServiceProvider
{



    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        Event::listen(

            CreateSuperAdmin::class,
            DatabaseMigrated::class
        );
        FilePreviewController::$middleware = ['web', 'universal', InitializeTenancyByDomain::class];



    }
}
