<?php

use \Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;




return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->statefulApi();


        $middleware->validateCsrfTokens(except: [
            'livewire/*'
        ]);

        $middleware->web(append: [
            StartSession::class,
            // InitializeTenancyByDomain::class,

            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // PreventAccessFromCentralDomains::class,
        ]);

        $middleware->api([
            InitializeTenancyByDomain::class,
        ]);
        
        $middleware->alias([

            'role' => RoleMiddleware::class,


        ]);


        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
