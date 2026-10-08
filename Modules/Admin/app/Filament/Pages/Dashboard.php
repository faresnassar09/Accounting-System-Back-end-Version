<?php

namespace Modules\Admin\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Accounting Executive Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getColumns(): int | array
    {
        return 2;
    }
}
