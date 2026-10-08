<?php

namespace Modules\Accounting\Services\Reports;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Budget;

class BudgetVsActualService
{
    public function generateReport(int $fiscalYear, ?int $branchId = null): array
    {
        $startDate = "{$fiscalYear}-01-01";
        $endDate   = "{$fiscalYear}-12-31";

        $budgets = Budget::query()
            ->with(['account.accountType', 'branch'])
            ->where('fiscal_year', $fiscalYear)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $rows = [];
        $totalBudgeted = 0.00;
        $totalActual = 0.00;

        foreach ($budgets as $budget) {
            $account = $budget->account;
            if (! $account) {
                continue;
            }

            $accGroup = $account->accountType?->account_group ?? 'expenses';

            // Query actual movement from ledger lines in the fiscal year
            $query = DB::table('journal_entry_lines as jl')
                ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->where('jl.account_id', $account->id)
                ->whereBetween('je.date', [$startDate, $endDate])
                ->where('je.type', '!=', 'closing')
                ->where('je.status', '!=', 'cancled')
                ->when($budget->branch_id, fn ($q, $bId) => $q->where('jl.branch_id', $bId));

            if ($accGroup === 'revenues') {
                $actual = (float) $query->selectRaw('COALESCE(SUM(jl.credit - jl.debit), 0.00) as bal')->value('bal');
            } else {
                $actual = (float) $query->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')->value('bal');
            }

            $budgeted = (float) $budget->allocated_amount;
            $variance = $budgeted - $actual;
            $utilization = $budgeted > 0 ? round(($actual / $budgeted) * 100, 1) : 0.0;

            // Status determination
            if ($accGroup === 'revenues') {
                $status = $actual >= $budgeted ? 'Target Achieved' : 'Below Target';
                $statusColor = $actual >= $budgeted ? 'emerald' : 'amber';
            } else {
                if ($actual > $budgeted) {
                    $status = 'Over Budget';
                    $statusColor = 'rose';
                } elseif ($utilization >= 85) {
                    $status = 'Near Limit';
                    $statusColor = 'amber';
                } else {
                    $status = 'Within Budget';
                    $statusColor = 'emerald';
                }
            }

            $rows[] = [
                'account_id'     => $account->id,
                'account_number' => $account->number,
                'account_name'   => $account->name,
                'account_group'  => $accGroup,
                'branch_name'    => $budget->branch?->name ?? 'Consolidated',
                'budgeted'       => $budgeted,
                'actual'         => $actual,
                'variance'       => $variance,
                'utilization'    => $utilization,
                'status'         => $status,
                'status_color'   => $statusColor,
            ];

            $totalBudgeted += $budgeted;
            $totalActual += $actual;
        }

        $overallVariance = $totalBudgeted - $totalActual;
        $overallUtilization = $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 1) : 0.0;

        return [
            'fiscal_year'         => $fiscalYear,
            'branch_id'           => $branchId,
            'total_budgeted'      => $totalBudgeted,
            'total_actual'        => $totalActual,
            'overall_variance'    => $overallVariance,
            'overall_utilization' => $overallUtilization,
            'items'               => $rows,
        ];
    }
}
