<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    Modules\Admin\Providers\Filament\AdminPanelProvider::class,
    Modules\SystemAdmin\Providers\Filament\SystemAdminPanelProvider::class,
    App\Providers\TenancyServiceProvider::class,

];    

