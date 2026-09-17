<?php

namespace Modules\Accounting\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BalanceSheetExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected array $data,
        protected ?string $endDate = null,
    ) {}

    public function view(): View
    {
        return view('accounting::reports.pdf.balance-sheet', [
            'data'    => $this->data,
            'endDate' => $this->endDate,
        ]);
    }
}
