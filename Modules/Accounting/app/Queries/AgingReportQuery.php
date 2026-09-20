<?php

namespace Modules\Accounting\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgingReportQuery
{
    /**
     * Compute aging summary for Accounts Receivable (AR) or Accounts Payable (AP).
     *
     * @param string $type 'receivable' (AR) or 'payable' (AP)
     * @param string|null $asOfDate Cutoff date (YYYY-MM-DD)
     * @param int|null $branchId Optional branch filter
     * @return array
     */
    public function getAgingData(string $type = 'receivable', ?string $asOfDate = null, ?int $branchId = null): array
    {
        $cutoff = $asOfDate ? Carbon::parse($asOfDate)->endOfDay() : now()->endOfDay();
        $cutoffDateStr = $cutoff->toDateString();

        $isAR = ($type === 'receivable');

        $query = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->leftJoin('branches as b', 'jl.branch_id', '=', 'b.id')
            ->where('je.status', 'approved')
            ->where('je.date', '<=', $cutoff);

        if ($branchId) {
            $query->where('jl.branch_id', $branchId);
        }

        if ($isAR) {
            // Accounts Receivable: Asset accounts with type current_assets or name containing Receivable
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('at.account_group', 'assets')
                        ->where('a.name', 'like', '%Receivable%');
                })->orWhere(function ($sub) {
                    $sub->where('at.type', 'current_assets')
                        ->where('a.name', 'like', '%Customer%');
                })->orWhere('at.type', 'current_assets');
            });
        } else {
            // Accounts Payable: Liability accounts with type current_liabilities or name containing Payable
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('at.account_group', 'liabilities')
                        ->where('a.name', 'like', '%Payable%');
                })->orWhere(function ($sub) {
                    $sub->where('at.type', 'current_liabilities')
                        ->where('a.name', 'like', '%Vendor%');
                })->orWhere('at.type', 'current_liabilities');
            });
        }

        $lines = $query->select([
            'jl.id',
            'jl.account_id',
            'a.name as account_name',
            'a.number as account_number',
            'jl.branch_id',
            'b.name as branch_name',
            'je.date as posting_date',
            'jl.debit',
            'jl.credit',
            'je.reference',
        ])->get();

        $accountsMap = [];
        $grandTotals = [
            'current'      => 0.0,
            'days_1_30'    => 0.0,
            'days_31_60'   => 0.0,
            'days_61_90'   => 0.0,
            'days_over_90' => 0.0,
            'total'        => 0.0,
        ];

        foreach ($lines as $line) {
            $net = $isAR
                ? ((float) $line->debit - (float) $line->credit)
                : ((float) $line->credit - (float) $line->debit);

            if (abs($net) < 0.001) {
                continue;
            }

            $lineDate = Carbon::parse($line->posting_date)->startOfDay();
            $ageDays = (int) $lineDate->diffInDays($cutoff->copy()->startOfDay(), false);

            $bucket = 'current';
            if ($ageDays > 90) {
                $bucket = 'days_over_90';
            } elseif ($ageDays > 60) {
                $bucket = 'days_61_90';
            } elseif ($ageDays > 30) {
                $bucket = 'days_31_60';
            } elseif ($ageDays > 0) {
                $bucket = 'days_1_30';
            }

            $accId = $line->account_id;
            if (!isset($accountsMap[$accId])) {
                $accountsMap[$accId] = [
                    'account_id'     => $accId,
                    'account_name'   => $line->account_name,
                    'account_number' => $line->account_number,
                    'branch_name'    => $line->branch_name ?? 'Consolidated',
                    'current'        => 0.0,
                    'days_1_30'      => 0.0,
                    'days_31_60'     => 0.0,
                    'days_61_90'     => 0.0,
                    'days_over_90'   => 0.0,
                    'total'          => 0.0,
                ];
            }

            $accountsMap[$accId][$bucket] += $net;
            $accountsMap[$accId]['total'] += $net;

            $grandTotals[$bucket] += $net;
            $grandTotals['total'] += $net;
        }

        // Format and clean roundings
        $rows = array_values(array_map(function ($row) {
            return [
                'account_id'     => $row['account_id'],
                'account_name'   => $row['account_name'],
                'account_number' => $row['account_number'],
                'branch_name'    => $row['branch_name'],
                'current'        => round($row['current'], 2),
                'days_1_30'      => round($row['days_1_30'], 2),
                'days_31_60'     => round($row['days_31_60'], 2),
                'days_61_90'     => round($row['days_61_90'], 2),
                'days_over_90'   => round($row['days_over_90'], 2),
                'total'          => round($row['total'], 2),
            ];
        }, $accountsMap));

        return [
            'type'        => $type,
            'type_label'  => $isAR ? 'Accounts Receivable (AR) Aging' : 'Accounts Payable (AP) Aging',
            'as_of_date'  => $cutoffDateStr,
            'branch_id'   => $branchId,
            'rows'        => $rows,
            'grand_total' => array_map(fn ($val) => round($val, 2), $grandTotals),
        ];
    }
}
