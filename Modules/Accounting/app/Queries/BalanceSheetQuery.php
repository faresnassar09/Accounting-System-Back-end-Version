<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class BalanceSheetQuery
{
    public function __invoke(string $endDate, float $netProfitValue, ?int $branchId = null): array
    {
        // 1. Shared base query for account cumulative balances
        $accountsSubQuery = DB::table('accounts as a')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->join('journal_entry_lines as ji', 'a.id', '=', 'ji.account_id')
            ->join('journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->whereDate('je.date', '<=', $endDate)
            ->whereIn('at.account_group', ['assets', 'liabilities', 'equity'])
            ->when($branchId, fn($q) => $q->where('ji.branch_id', $branchId))
            ->select('a.id', 'a.name', 'at.type')
            ->selectRaw('ABS(SUM(ji.debit - ji.credit)) as netBalance')
            ->groupBy('a.id', 'a.name', 'at.type');

        // 2. Forked query: Fetch account line items
        $accounts = (clone $accountsSubQuery)->get();

        // 3. Forked query: Database-computed group & sub-type totals (ZERO PHP math)
        $absNetProfit = abs($netProfitValue);
        $totals = DB::query()->fromSub($accountsSubQuery, 'acc')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN acc.type = 'current_assets' THEN acc.netBalance ELSE 0 END), 0.00) as current_assets,
                COALESCE(SUM(CASE WHEN acc.type = 'non_current_assets' THEN acc.netBalance ELSE 0 END), 0.00) as non_current_assets,
                COALESCE(SUM(CASE WHEN acc.type = 'current_liabilities' THEN acc.netBalance ELSE 0 END), 0.00) as current_liabilities,
                COALESCE(SUM(CASE WHEN acc.type = 'non_current_liabilities' THEN acc.netBalance ELSE 0 END), 0.00) as non_current_liabilities,
                COALESCE(SUM(CASE WHEN acc.type = 'equity_capital' THEN acc.netBalance ELSE 0 END), 0.00) as equity_capital,
                COALESCE(SUM(CASE WHEN acc.type = 'retained_earnings' THEN acc.netBalance ELSE 0 END), 0.00) as retained_earnings,
                COALESCE(SUM(CASE WHEN acc.type = 'opening_balance_diff' THEN acc.netBalance ELSE 0 END), 0.00) as opening_balance_diff,

                COALESCE(SUM(CASE WHEN acc.type IN ('current_assets', 'non_current_assets') THEN acc.netBalance ELSE 0 END), 0.00) as total_assets,
                COALESCE(SUM(CASE WHEN acc.type IN ('current_liabilities', 'non_current_liabilities') THEN acc.netBalance ELSE 0 END), 0.00) as total_liabilities,
                COALESCE(SUM(CASE WHEN acc.type IN ('equity_capital', 'retained_earnings', 'opening_balance_diff') THEN acc.netBalance ELSE 0 END), 0.00) as total_equity_base,
                (COALESCE(SUM(CASE WHEN acc.type IN ('equity_capital', 'retained_earnings', 'opening_balance_diff') THEN acc.netBalance ELSE 0 END), 0.00) + ?) as total_equity,
                (COALESCE(SUM(CASE WHEN acc.type IN ('current_liabilities', 'non_current_liabilities', 'equity_capital', 'retained_earnings', 'opening_balance_diff') THEN acc.netBalance ELSE 0 END), 0.00) + ?) as total_liabilities_and_equity
            ", [$absNetProfit, $absNetProfit])
            ->first();

        $groupedAccounts = $accounts->groupBy('type');

        return [
            'assets_group' => [
                'group_code'  => 'assets',
                'group_name'  => 'assets',
                'group_total' => (float) ($totals->total_assets ?? 0.00),
                'sub_types'   => [
                    [
                        'type_code'  => 'current_assets',
                        'type_name'  => 'Current Assets',
                        'type_total' => (float) ($totals->current_assets ?? 0.00),
                        'accounts'   => $groupedAccounts->get('current_assets', collect())->values(),
                    ],
                    [
                        'type_code'  => 'non_current_assets',
                        'type_name'  => 'Non Current Assets',
                        'type_total' => (float) ($totals->non_current_assets ?? 0.00),
                        'accounts'   => $groupedAccounts->get('non_current_assets', collect())->values(),
                    ],
                ],
            ],
            'liabilities_and_equity_group' => [
                'group_code'  => 'liabilities_and_equity',
                'group_name'  => 'Liabilities And Equity',
                'group_total' => (float) ($totals->total_liabilities_and_equity ?? $absNetProfit),
                'sub_types'   => [
                    'liabilities_group' => [
                        'type_code'  => 'current_liabilities',
                        'type_name'  => 'Current Liabilities',
                        'type_total' => (float) ($totals->total_liabilities ?? 0.00),
                        'accounts'   => $groupedAccounts->get('current_liabilities', collect())->values(),
                    ],
                    'equity_group' => [
                        'type_code'  => 'equity',
                        'type_name'  => 'Owners Rights',
                        'type_total' => (float) ($totals->total_equity ?? $absNetProfit),
                        'accounts'   => $groupedAccounts->get('equity_capital', collect())
                            ->merge($groupedAccounts->get('retained_earnings', collect()))
                            ->push([
                                'id'         => null,
                                'name'       => 'Net Profit / Loss (Current)',
                                'netBalance' => $netProfitValue,
                            ]),
                    ],
                ],
            ],
        ];
    }
}
