<?php

namespace Modules\Admin\Filament\Widgets;

use Filament\Widgets\Widget;
use Modules\Accounting\Models\ClosedFinancialYear;

class AccountingOverviewHeaderWidget extends Widget
{
    protected string $view = 'admin::filament.widgets.accounting-overview-header';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getCurrentYearProperty(): int
    {
        return (int) now()->format('Y');
    }

    public function getIsYearClosedProperty(): bool
    {
        return ClosedFinancialYear::where('year', $this->currentYear)->exists();
    }

    public function getClosedInfoProperty(): ?ClosedFinancialYear
    {
        return ClosedFinancialYear::where('year', $this->currentYear)->first();
    }

    public function getTenantIdentifierProperty(): string
    {
        return tenancy()->tenant?->id ?? config('app.name', 'Accounting System');
    }

    public function getAdminNameProperty(): string
    {
        return auth('admin')->user()?->name ?? 'Administrator';
    }
}
