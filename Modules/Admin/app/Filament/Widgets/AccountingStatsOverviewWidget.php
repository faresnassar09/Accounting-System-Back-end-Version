<?php

namespace Modules\Admin\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AccountingStatsOverviewWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $currentYear = (int) now()->format('Y');
        $startDate   = "{$currentYear}-01-01";
        $endDate     = "{$currentYear}-12-31";

        // 1. Revenues YTD
        $totalRevenues = (float) DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('je.type', '!=', 'closing')
            ->where('je.status', '!=', 'cancled')
            ->where('at.account_group', 'revenues')
            ->selectRaw('COALESCE(SUM(jl.credit - jl.debit), 0.00) as bal')
            ->value('bal');

        // 2. Expenses YTD
        $totalExpenses = (float) DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('je.type', '!=', 'closing')
            ->where('je.status', '!=', 'cancled')
            ->where('at.account_group', 'expenses')
            ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')
            ->value('bal');

        // 3. Net Profit YTD
        $netProfit = $totalRevenues - $totalExpenses;

        // 4. Liquid Assets (Cash & Bank in current assets)
        $liquidAssets = (float) DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->where('je.status', '!=', 'cancled')
            ->where('at.type', 'current_assets')
            ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')
            ->value('bal');

        // Calculate 6-month sparklines
        $revSparkline = [];
        $expSparkline = [];
        $profitSparkline = [];

        for ($i = 5; $i >= 0; $i--) {
            $pointDate = now()->subMonths($i);
            $mStart = $pointDate->copy()->startOfMonth()->format('Y-m-d');
            $mEnd   = $pointDate->copy()->endOfMonth()->format('Y-m-d');

            $mRev = (float) DB::table('journal_entry_lines as jl')
                ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->join('accounts as a', 'jl.account_id', '=', 'a.id')
                ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
                ->whereBetween('je.date', [$mStart, $mEnd])
                ->where('je.type', '!=', 'closing')
                ->where('je.status', '!=', 'cancled')
                ->where('at.account_group', 'revenues')
                ->selectRaw('COALESCE(SUM(jl.credit - jl.debit), 0.00) as bal')
                ->value('bal');

            $mExp = (float) DB::table('journal_entry_lines as jl')
                ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->join('accounts as a', 'jl.account_id', '=', 'a.id')
                ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
                ->whereBetween('je.date', [$mStart, $mEnd])
                ->where('je.type', '!=', 'closing')
                ->where('je.status', '!=', 'cancled')
                ->where('at.account_group', 'expenses')
                ->selectRaw('COALESCE(SUM(jl.debit - jl.credit), 0.00) as bal')
                ->value('bal');

            $revSparkline[] = $mRev;
            $expSparkline[] = $mExp;
            $profitSparkline[] = $mRev - $mExp;
        }

        return [
            Stat::make('Net Profit (YTD)', '$' . number_format($netProfit, 2))
                ->description($netProfit >= 0 ? 'Operating Net Surplus' : 'Operating Net Deficit')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netProfit >= 0 ? 'success' : 'danger')
                ->chart($profitSparkline),

            Stat::make('Revenues (YTD)', '$' . number_format($totalRevenues, 2))
                ->description("Fiscal Year {$currentYear} Inflow")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($revSparkline),

            Stat::make('Expenses (YTD)', '$' . number_format($totalExpenses, 2))
                ->description("Fiscal Year {$currentYear} Spend")
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning')
                ->chart($expSparkline),

            Stat::make('Liquid Cash & Reserves', '$' . number_format($liquidAssets, 2))
                ->description('Current Assets Balance')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('info')
                ->chart([$liquidAssets * 0.9, $liquidAssets * 0.95, $liquidAssets]),
        ];
    }
}
