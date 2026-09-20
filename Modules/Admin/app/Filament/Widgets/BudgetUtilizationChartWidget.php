<?php

namespace Modules\Admin\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Budget;

class BudgetUtilizationChartWidget extends ChartWidget
{
    protected ?string $heading = 'Budget vs. Actuals';

    protected ?string $description = 'Allocated targets compared to actual spend for key accounts';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $currentYear = (int) now()->format('Y');
        $startDate   = "{$currentYear}-01-01";
        $endDate     = "{$currentYear}-12-31";

        $budgets = Budget::query()
            ->with(['account.accountType'])
            ->where('fiscal_year', $currentYear)
            ->orderByDesc('allocated_amount')
            ->limit(6)
            ->get();

        if ($budgets->isEmpty()) {
            return [
                'datasets' => [],
                'labels'   => [],
            ];
        }

        $labels          = [];
        $budgetedAmounts = [];
        $actualAmounts   = [];

        foreach ($budgets as $budget) {
            $account = $budget->account;
            if (! $account) {
                continue;
            }

            $labels[] = "#{$account->number} " . mb_strimwidth($account->name, 0, 16, '...');
            $budgetedAmounts[] = (float) $budget->allocated_amount;

            $group = $account->accountType?->account_group ?? 'expenses';

            $q = DB::table('journal_entry_lines as jl')
                ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->where('jl.account_id', $account->id)
                ->whereBetween('je.date', [$startDate, $endDate])
                ->where('je.type', '!=', 'closing')
                ->where('je.status', '!=', 'cancled');

            if ($group === 'revenues') {
                $actual = (float) $q->selectRaw('COALESCE(SUM(jl.credit - jl.debit), 0.00) as bal')->value('bal');
            } else {
                $actual = (float) $q->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')->value('bal');
            }

            $actualAmounts[] = $actual;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Budget Allocated ($)',
                    'data'            => $budgetedAmounts,
                    'backgroundColor' => '#6366f1',
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Actual Movement ($)',
                    'data'            => $actualAmounts,
                    'backgroundColor' => '#f59e0b',
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
