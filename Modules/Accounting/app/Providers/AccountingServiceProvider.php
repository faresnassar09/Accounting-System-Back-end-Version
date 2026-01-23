<?php

namespace Modules\Accounting\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Modules\Accounting\Http\Middleware\PreventActionOnClosedYearMiddleware;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Observers\AccountObserver;
use Modules\Accounting\Observers\JournalEntryLineObserver;
use Modules\Accounting\Observers\JournalEntryObserver;
use Modules\Accounting\Policies\ChartAccountingPolicy;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\BalanceSheetRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Repositories\Contracts\GeneralLdegerRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\IncomeStatementRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\TrialBalanceRepositoryInterface;
use Modules\Accounting\Repositories\Eloquent\AccountRepository;
use Modules\Accounting\Repositories\Eloquent\BalanceSheetRepository;
use Modules\Accounting\Repositories\Eloquent\FinancialClosingRepository;
use Modules\Accounting\Repositories\Eloquent\GeneralLedgerRepository;
use Modules\Accounting\Repositories\Eloquent\IncomeStatementRepository;
use Modules\Accounting\Repositories\Eloquent\JournalEntryRepository;

use Modules\Accounting\Repositories\Eloquent\TrialBalanceRepository;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AccountingServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Accounting';

    protected string $nameLower = 'accounting';

    protected $policies = [

        Account::class  => ChartAccountingPolicy::class,
    ];
  
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        Account::observe(AccountObserver::class);
        JournalEntryLine::observe(JournalEntryLineObserver::class);
        JournalEntry::observe(JournalEntryObserver::class);

        $this->registerPolicies();
        $this->app['router']->aliasMiddleware('check_year', PreventActionOnClosedYearMiddleware::class);

    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {  
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        
        $this->app->singleton(
            AccountRepositoryInterface::class,
            AccountRepository::class);

            $this->app->singleton(
            JournalEntryRepositoryInterface::class,
            JournalEntryRepository::class);

            $this->app->singleton(
                GeneralLdegerRepositoryInterface::class,
                GeneralLedgerRepository::class
            );

            $this->app->singleton(
                TrialBalanceRepositoryInterface::class,
                TrialBalanceRepository::class
            );

            $this->app->singleton(
                IncomeStatementRepositoryInterface::class,
                IncomeStatementRepository::class
            );

            $this->app->singleton(

                BalanceSheetRepositoryInterface::class,
                BalanceSheetRepository::class
            );

            $this->app->singleton(
                
                FinancialClosingReposiroryInterface::class,
                FinancialClosingRepository::class
                
            );
            
    }


    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
