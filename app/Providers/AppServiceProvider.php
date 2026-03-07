<?php

namespace App\Providers;

use App\Models\Passport\AuthCode;
use App\Models\Passport\Client;
use App\Models\Passport\DeviceCode;
use App\Models\Passport\RefreshToken;
use App\Models\Passport\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Stancl\Tenancy\Events\TenancyInitialized;

class AppServiceProvider extends ServiceProvider
{



    /**
     * Register any application services.
     */
    public function register(): void
    {
// Passport::ignoreRoutes(); 

}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    Passport::useTokenModel(Token::class);

    Passport::useRefreshTokenModel(RefreshToken::class);

    Passport::useAuthCodeModel(AuthCode::class);

    Passport::useClientModel(Client::class);

    Passport::useDeviceCodeModel(DeviceCode::class);


    }
}
