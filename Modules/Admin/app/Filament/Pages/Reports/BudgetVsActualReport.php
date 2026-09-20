<?php

namespace Modules\Admin\Filament\Pages\Reports;

use BackedEnum;
use Filament\Pages\Page;
use Modules\Accounting\Services\Reports\BudgetVsActualService;
use Modules\Branch\Models\Branch;
use UnitEnum;

class BudgetVsActualReport extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 5;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Budget vs. Actuals';

    protected static ?string $title = 'Budget vs. Actual Variance Report';

    protected static ?string $slug = 'reports/budget-vs-actual';

    protected string $view = 'admin::filament.pages.reports.budget-vs-actual';

    public int $fiscalYear;

    public ?int $branchId = null;

    public function mount(): void
    {
        $this->fiscalYear = (int) now()->format('Y');
        $this->branchId = null;
    }

    public function getAvailableYearsProperty(): array
    {
        $currentYear = (int) now()->format('Y');
        $years = [];
        for ($y = $currentYear + 1; $y >= $currentYear - 5; $y--) {
            $years[$y] = (string) $y;
        }
        return $years;
    }

    public function getBranchesProperty()
    {
        return Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id');
    }

    public function getReportDataProperty(): array
    {
        return app(BudgetVsActualService::class)->generateReport(
            fiscalYear: $this->fiscalYear,
            branchId: $this->branchId ? (int) $this->branchId : null
        );
    }
}
