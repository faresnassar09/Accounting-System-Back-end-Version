<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\Reports\IncomeStatementService;

class CashFlowStatementQuery
{
    public function __invoke(string $startDate, string $endDate, ?int $branchId = null): array
    {
        // 1. Identify Cash & Cash Equivalents accounts
        $allCurrentAssets = DB::table('accounts as a')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->where('at.type', 'current_assets')
            ->select('a.id', 'a.number', 'a.name')
            ->get();

        $cashAccounts = $allCurrentAssets->filter(function ($acc) {
            $name = mb_strtolower($acc->name);
            return str_contains($name, 'cash') ||
                   str_contains($name, 'bank') ||
                   str_contains($name, 'treasury') ||
                   str_contains($name, 'petty') ||
                   str_contains($name, 'صندوق') ||
                   str_contains($name, 'بنك') ||
                   str_contains($name, 'نقد');
        });

        if ($cashAccounts->isEmpty() && $allCurrentAssets->isNotEmpty()) {
            $cashAccounts = collect([$allCurrentAssets->sortBy('number')->first()]);
        }

        $cashAccountIds = $cashAccounts->pluck('id')->all();

        // 2. Beginning & Ending Cash
        $beginningCash = empty($cashAccountIds) ? 0.00 : (float) DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccountIds)
            ->where('je.date', '<', $startDate)
            ->where('je.status', '!=', 'cancled')
            ->when($branchId, fn ($q) => $q->where('jl.branch_id', $branchId))
            ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')
            ->value('bal');

        $endingCash = empty($cashAccountIds) ? 0.00 : (float) DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccountIds)
            ->where('je.date', '<=', $endDate)
            ->where('je.status', '!=', 'cancled')
            ->when($branchId, fn ($q) => $q->where('jl.branch_id', $branchId))
            ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')
            ->value('bal');

        $actualNetCashChange = $endingCash - $beginningCash;

        // 3. Operating Activities: Net Income for the period
        $incomeReport = app(IncomeStatementService::class)->generateReport($startDate, $endDate, $branchId);
        $netIncome = (float) ($incomeReport['net_income'] ?? 0.00);

        // 4. Non-cash adjustments: Depreciation & Amortization
        $depreciation = (float) DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('je.type', '!=', 'closing')
            ->where('je.status', '!=', 'cancled')
            ->where('at.account_group', 'expenses')
            ->where(function ($q) {
                $q->where('a.name', 'LIKE', '%depreciation%')
                  ->orWhere('a.name', 'LIKE', '%amortization%')
                  ->orWhere('a.name', 'LIKE', '%إهلاك%');
            })
            ->when($branchId, fn ($q) => $q->where('jl.branch_id', $branchId))
            ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')
            ->value('bal');

        // Helper to query account balance at a specific cutoff date
        $getAccountBalances = function (array $accountTypes, string $cutoffDate, bool $isCreditNormal = false) use ($branchId) {
            $linesSub = DB::table('journal_entry_lines as jl')
                ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->where('je.date', '<=', $cutoffDate)
                ->where('je.status', '!=', 'cancled')
                ->when($branchId, fn ($q) => $q->where('jl.branch_id', $branchId))
                ->groupBy('jl.account_id')
                ->select([
                    'jl.account_id',
                    DB::raw('SUM(jl.debit) as total_debit'),
                    DB::raw('SUM(jl.credit) as total_credit'),
                ]);

            return DB::table('accounts as a')
                ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
                ->leftJoinSub($linesSub, 'lines', 'a.id', '=', 'lines.account_id')
                ->whereIn('at.type', $accountTypes)
                ->select([
                    'a.id',
                    'a.number',
                    'a.name',
                    'at.type',
                    DB::raw($isCreditNormal
                        ? 'COALESCE(lines.total_credit - lines.total_debit, 0.00) as balance'
                        : 'COALESCE(lines.total_debit - lines.total_credit, 0.00) as balance'
                    ),
                ])
                ->get()
                ->keyBy('id');
        };

        // Day before startDate for beginning balances
        $priorDate = date('Y-m-d', strtotime($startDate . ' -1 day'));

        // 5. Working Capital: Non-cash Current Assets
        $nonCashAssetIds = $allCurrentAssets->whereNotIn('id', $cashAccountIds)->pluck('id')->all();
        $currAssetsStart = empty($nonCashAssetIds) ? collect() : $getAccountBalances(['current_assets'], $priorDate, false)->only($nonCashAssetIds);
        $currAssetsEnd   = empty($nonCashAssetIds) ? collect() : $getAccountBalances(['current_assets'], $endDate, false)->only($nonCashAssetIds);

        $operatingAssetItems = [];
        $totalOperatingAssetsCashEffect = 0.00;

        foreach ($currAssetsEnd as $id => $endRow) {
            $startBal = (float) ($currAssetsStart[$id]->balance ?? 0.00);
            $endBal   = (float) $endRow->balance;
            $delta    = $endBal - $startBal;
            $cashEffect = $startBal - $endBal; // Asset increase -> cash outflow

            if (abs($delta) > 0.001 || abs($endBal) > 0.001) {
                $operatingAssetItems[] = [
                    'account_id'     => $endRow->id,
                    'account_number' => $endRow->number,
                    'account_name'   => $endRow->name,
                    'start_balance'  => $startBal,
                    'end_balance'    => $endBal,
                    'delta'          => $delta,
                    'cash_effect'    => $cashEffect,
                ];
                $totalOperatingAssetsCashEffect += $cashEffect;
            }
        }

        // 6. Working Capital: Current Liabilities
        $currLiabStart = $getAccountBalances(['current_liabilities'], $priorDate, true);
        $currLiabEnd   = $getAccountBalances(['current_liabilities'], $endDate, true);

        $operatingLiabItems = [];
        $totalOperatingLiabCashEffect = 0.00;

        foreach ($currLiabEnd as $id => $endRow) {
            $startBal = (float) ($currLiabStart[$id]->balance ?? 0.00);
            $endBal   = (float) $endRow->balance;
            $delta    = $endBal - $startBal;
            $cashEffect = $endBal - $startBal; // Liability increase -> cash inflow

            if (abs($delta) > 0.001 || abs($endBal) > 0.001) {
                $operatingLiabItems[] = [
                    'account_id'     => $endRow->id,
                    'account_number' => $endRow->number,
                    'account_name'   => $endRow->name,
                    'start_balance'  => $startBal,
                    'end_balance'    => $endBal,
                    'delta'          => $delta,
                    'cash_effect'    => $cashEffect,
                ];
                $totalOperatingLiabCashEffect += $cashEffect;
            }
        }

        $totalWorkingCapitalCashEffect = $totalOperatingAssetsCashEffect + $totalOperatingLiabCashEffect;
        $netOperatingCashFlow = $netIncome + $depreciation + $totalWorkingCapitalCashEffect;

        // 7. Investing Activities: Non-Current Assets
        $nonCurrAssetsStart = $getAccountBalances(['non_current_assets'], $priorDate, false);
        $nonCurrAssetsEnd   = $getAccountBalances(['non_current_assets'], $endDate, false);

        $investingItems = [];
        $netInvestingCashFlow = 0.00;

        foreach ($nonCurrAssetsEnd as $id => $endRow) {
            $startBal = (float) ($nonCurrAssetsStart[$id]->balance ?? 0.00);
            $endBal   = (float) $endRow->balance;
            $delta    = $endBal - $startBal;
            $cashEffect = $startBal - $endBal; // Capital addition -> cash outflow

            if (abs($delta) > 0.001 || abs($endBal) > 0.001) {
                $investingItems[] = [
                    'account_id'     => $endRow->id,
                    'account_number' => $endRow->number,
                    'account_name'   => $endRow->name,
                    'start_balance'  => $startBal,
                    'end_balance'    => $endBal,
                    'delta'          => $delta,
                    'cash_effect'    => $cashEffect,
                ];
                $netInvestingCashFlow += $cashEffect;
            }
        }

        // 8. Financing Activities: Non-Current Liabilities & Equity Capital
        $financingLiabStart = $getAccountBalances(['non_current_liabilities'], $priorDate, true);
        $financingLiabEnd   = $getAccountBalances(['non_current_liabilities'], $endDate, true);

        $equityStart = $getAccountBalances(['equity_capital'], $priorDate, true);
        $equityEnd   = $getAccountBalances(['equity_capital'], $endDate, true);

        $financingItems = [];
        $netFinancingCashFlow = 0.00;

        // Non-current liabilities (Loans, Notes)
        foreach ($financingLiabEnd as $id => $endRow) {
            $startBal = (float) ($financingLiabStart[$id]->balance ?? 0.00);
            $endBal   = (float) $endRow->balance;
            $delta    = $endBal - $startBal;
            $cashEffect = $endBal - $startBal;

            if (abs($delta) > 0.001 || abs($endBal) > 0.001) {
                $financingItems[] = [
                    'account_id'     => $endRow->id,
                    'account_number' => $endRow->number,
                    'account_name'   => $endRow->name . ' (Borrowings/Repayments)',
                    'start_balance'  => $startBal,
                    'end_balance'    => $endBal,
                    'delta'          => $delta,
                    'cash_effect'    => $cashEffect,
                ];
                $netFinancingCashFlow += $cashEffect;
            }
        }

        // Equity Capital contributions
        foreach ($equityEnd as $id => $endRow) {
            $startBal = (float) ($equityStart[$id]->balance ?? 0.00);
            $endBal   = (float) $endRow->balance;
            $delta    = $endBal - $startBal;
            $cashEffect = $endBal - $startBal;

            if (abs($delta) > 0.001 || abs($endBal) > 0.001) {
                $financingItems[] = [
                    'account_id'     => $endRow->id,
                    'account_number' => $endRow->number,
                    'account_name'   => $endRow->name . ' (Capital Inflow/Draws)',
                    'start_balance'  => $startBal,
                    'end_balance'    => $endBal,
                    'delta'          => $delta,
                    'cash_effect'    => $cashEffect,
                ];
                $netFinancingCashFlow += $cashEffect;
            }
        }

        // 9. Reconciliation
        $computedNetCashFlow = $netOperatingCashFlow + $netInvestingCashFlow + $netFinancingCashFlow;
        $reconciledEndingCash = $beginningCash + $computedNetCashFlow;
        $variance = round($reconciledEndingCash - $endingCash, 2);
        $isBalanced = abs($variance) < 0.01;

        return [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'branch_id'  => $branchId,

            'cash_accounts' => $cashAccounts->map(fn ($a) => [
                'id'     => $a->id,
                'number' => $a->number,
                'name'   => $a->name,
            ])->values()->all(),

            'beginning_cash' => $beginningCash,
            'ending_cash'    => $endingCash,
            'actual_net_change' => $actualNetCashChange,

            // Operating section
            'net_income'             => $netIncome,
            'depreciation'           => $depreciation,
            'operating_asset_items'  => $operatingAssetItems,
            'operating_liab_items'   => $operatingLiabItems,
            'working_capital_change' => $totalWorkingCapitalCashEffect,
            'net_operating_cash_flow'=> $netOperatingCashFlow,

            // Investing section
            'investing_items'        => $investingItems,
            'net_investing_cash_flow'=> $netInvestingCashFlow,

            // Financing section
            'financing_items'        => $financingItems,
            'net_financing_cash_flow'=> $netFinancingCashFlow,

            // Summary
            'computed_net_cash_flow' => $computedNetCashFlow,
            'reconciled_ending_cash' => $reconciledEndingCash,
            'variance'               => $variance,
            'is_balanced'            => $isBalanced,
        ];
    }
}
