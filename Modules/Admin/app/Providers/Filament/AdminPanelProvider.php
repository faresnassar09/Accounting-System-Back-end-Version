<?php

namespace Modules\Admin\Providers\Filament;
 
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use GuzzleHttp\Middleware;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Admin\Models\Admin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            // ->domain('app.localhost')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: base_path('Modules/Admin/app/Filament/Resources'), for: 'Modules\Admin\Filament\Resources')
            ->discoverPages(in: base_path('Modules/Admin/app/Filament/Pages'), for: 'Modules\Admin\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])  
            ->discoverWidgets(in: base_path('Modules/Admin/app/Filament/Widgets'), for: 'Modules\Admin\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            
            ->middleware([

                InitializeTenancyByDomain::class,

                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                verifyCsrfToken::class,

                AuthenticateSession::class,

                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,


            ])
            
            ->authMiddleware([
                Authenticate::class,

            ])


;
    }


}
