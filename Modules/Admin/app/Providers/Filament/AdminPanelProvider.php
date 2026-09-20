<?php

namespace Modules\Admin\Providers\Filament;
 
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Modules\Admin\Filament\Pages\Dashboard;
use Modules\Admin\Filament\Pages\FinancialClosing\FinancialClosingPage;
use Modules\Admin\Filament\Pages\Reports\BalanceSheetReport;
use Modules\Admin\Filament\Pages\Reports\BudgetVsActualReport;
use Modules\Admin\Filament\Pages\Reports\GeneralLedgerReport;
use Modules\Admin\Filament\Pages\Reports\IncomeStatementReport;
use Modules\Admin\Filament\Pages\Reports\TrialBalanceReport;
use Modules\Admin\Filament\Widgets\AccountingOverviewHeaderWidget;
use Modules\Admin\Filament\Widgets\AccountingStatsOverviewWidget;
use Modules\Admin\Filament\Widgets\BudgetUtilizationChartWidget;
use Modules\Admin\Filament\Widgets\CashflowTrendChartWidget;
use Modules\Admin\Filament\Widgets\LatestJournalEntriesWidget;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

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
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: base_path('Modules/Admin/app/Filament/Resources'), for: 'Modules\Admin\Filament\Resources')
            ->discoverPages(in: base_path('Modules/Admin/app/Filament/Pages'), for: 'Modules\Admin\Filament\Pages')
            ->pages([
                Dashboard::class,
                FinancialClosingPage::class,
                TrialBalanceReport::class,
                GeneralLedgerReport::class,
                IncomeStatementReport::class,
                BalanceSheetReport::class,
                BudgetVsActualReport::class,
            ])  
            ->discoverWidgets(in: base_path('Modules/Admin/app/Filament/Widgets'), for: 'Modules\Admin\Filament\Widgets')
            ->widgets([
                AccountingOverviewHeaderWidget::class,
                AccountingStatsOverviewWidget::class,
                CashflowTrendChartWidget::class,
                BudgetUtilizationChartWidget::class,
                LatestJournalEntriesWidget::class,
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
