<?php

namespace Modules\Accounting\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class IncomeStatementExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected ?string $startDate = null,
        protected ?string $endDate = null,
    ) {}

    public function view(): View
    {
        return view('accounting::reports.excel.income-statement', [
            'data'      => $this->data,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
