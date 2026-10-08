<?php

namespace Modules\Accounting\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounting\Exports\AgingExport;
use Modules\Accounting\Queries\AgingReportQuery;

class AgingReportService
{
    public function __construct(
        protected AgingReportQuery $agingQuery,
    ) {}

    public function getAgingReport(string $type = 'receivable', ?string $asOfDate = null, ?int $branchId = null): array
    {
        return $this->agingQuery->getAgingData($type, $asOfDate, $branchId);
    }

    public function generatePdf(array $data)
    {
        return Pdf::loadView('accounting::reports.pdf.aging', ['report' => $data]);
    }

    public function generateExcel(array $data)
    {
        return Excel::download(new AgingExport($data), "aging-report-{$data['type']}-{$data['as_of_date']}.xlsx");
    }
}
