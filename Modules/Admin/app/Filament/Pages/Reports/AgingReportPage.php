<?php

namespace Modules\Admin\Filament\Pages\Reports;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Modules\Accounting\Services\Reports\AgingReportService;
use Modules\Branch\Models\Branch;
use UnitEnum;

class AgingReportPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 6;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Aging Analysis (AR / AP)';

    protected static ?string $title = 'Accounts Receivable & Payable Aging Analysis';

    protected static ?string $slug = 'reports/aging';

    protected string $view = 'admin::filament.pages.reports.aging';

    public string $type = 'receivable';

    public ?string $asOfDate = null;

    public ?int $branchId = null;

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->type     = 'receivable';
        $this->branchId = null;
    }

    public function getReportData(): array
    {
        return app(AgingReportService::class)->getAgingReport(
            type: $this->type,
            asOfDate: $this->asOfDate,
            branchId: $this->branchId ? (int) $this->branchId : null
        );
    }

    public function getBranchesProperty()
    {
        return Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $data = $this->getReportData();
                    return response()->streamDownload(function () use ($data) {
                        echo app(AgingReportService::class)->generatePdf($data)->output();
                    }, "aging-{$this->type}-{$this->asOfDate}.pdf");
                }),

            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $data = $this->getReportData();
                    return app(AgingReportService::class)->generateExcel($data);
                }),
        ];
    }
}
