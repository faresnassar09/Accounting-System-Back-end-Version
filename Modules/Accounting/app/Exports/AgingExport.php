<?php

namespace Modules\Accounting\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AgingExport implements FromView, ShouldAutoSize
{
    public function __construct(protected array $data) {}

    public function view(): View
    {
        return view('accounting::reports.excel.aging', [
            'report' => $this->data,
        ]);
    }
}
