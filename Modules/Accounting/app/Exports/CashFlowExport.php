<?php

namespace Modules\Accounting\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CashFlowExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected array $data,
    ) {}

    public function view(): View
    {
        return view('accounting::reports.excel.cash-flow', [
            'data' => $this->data,
        ]);
    }
}
