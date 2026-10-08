<?php

namespace Modules\Admin\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CashflowTrendChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue vs. Expense Trend';

    protected ?string $description = 'Monthly breakdown of operational income and expenditures for the active year';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $currentYear = (int) now()->format('Y');
        $startDate   = "{$currentYear}-01-01";
        $endDate     = "{$currentYear}-12-31";

        $records = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('je.type', '!=', 'closing')
            ->where('je.status', '!=', 'cancled')
            ->whereIn('at.account_group', ['revenues', 'expenses'])
            ->select([
                'je.date',
                'at.account_group',
                'jl.debit',
                'jl.credit',
            ])
            ->get();

        $monthlyRev = array_fill(1, 12, 0.0);
        $monthlyExp = array_fill(1, 12, 0.0);

        foreach ($records as $row) {
            $month = (int) date('n', strtotime($row->date));
            if ($row->account_group === 'revenues') {
                $monthlyRev[$month] += ((float) $row->credit - (float) $row->debit);
            } else {
                $monthlyExp[$month] += ((float) $row->debit - (float) $row->credit);
            }
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Operating Revenues ($)',
                    'data'            => array_values($monthlyRev),
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill'            => 'start',
                    'tension'         => 0.35,
                ],
                [
                    'label'           => 'Operating Expenses ($)',
                    'data'            => array_values($monthlyExp),
                    'borderColor'     => '#f43f5e',
                    'backgroundColor' => 'rgba(244, 63, 94, 0.1)',
                    'fill'            => 'start',
                    'tension'         => 0.35,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
