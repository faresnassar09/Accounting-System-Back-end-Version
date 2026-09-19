<x-filament-panels::page>
    @php
        $reportData = $this->getReportData();
        $totalRevenue = (float) ($reportData['total_revenue'] ?? 0);
        $grossSales = (float) ($reportData['gross_sales'] ?? 0);
        $salesDeductions = (float) ($reportData['sales_deductions'] ?? 0);
        $netSales = (float) ($reportData['net_sales'] ?? ($grossSales - $salesDeductions));
        $operatingRevenue = (float) ($reportData['operating_revenue'] ?? 0);
        $operatingRevenues = $reportData['operating_revenue_details'] ?? [];

        $totalCogs = (float) ($reportData['total_cogs'] ?? 0);
        $cogsAccounts = $reportData['cogs_details'] ?? [];

        $grossProfit = (float) ($reportData['gross_profit'] ?? ($totalRevenue - $totalCogs));

        $totalExpenses = (float) ($reportData['total_expenses'] ?? 0);
        $operatingExpenses = $reportData['operating_expenses_details'] ?? [];

        $operatingIncome = (float) ($reportData['operating_income'] ?? ($grossProfit - $totalExpenses));

        $netIncome = (float) ($reportData['net_income'] ?? $operatingIncome);
        $isProfit = ($netIncome >= 0);
        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
        
        $startDateFormatted = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->format('M d, Y') : 'Start of Period';
        $endDateFormatted = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('M d, Y') : now()->format('M d, Y');
    @endphp

    <div class="space-y-6">
        {{-- Professional Executive Report Header --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-6 text-white shadow-lg ring-1 ring-white/10">
            <div class="absolute -right-8 -top-8 h-48 w-48 rounded-full bg-primary-500/10 blur-2xl"></div>
            <div class="relative flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-primary-500/20 px-2 py-0.5 text-xs font-semibold text-primary-300 ring-1 ring-inset ring-primary-500/30">
                            {{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs font-medium text-slate-300">{{ $selectedBranch }}</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">
                        INCOME STATEMENT (PROFIT &amp; LOSS)
                    </h1>
                    <p class="mt-1 text-xs text-slate-300">
                        Financial performance for the period <span class="font-semibold text-white">{{ $startDateFormatted }}</span> to <span class="font-semibold text-white">{{ $endDateFormatted }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Currency</div>
                        <div class="text-sm font-black text-white">USD ($)</div>
                    </div>
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bottom Line Result</div>
                        @if($isProfit)
                            <div class="inline-flex items-center gap-1.5 text-sm font-black text-emerald-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Net Income (Profit)
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 text-sm font-black text-rose-400">
                                <span class="h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                                Net Loss
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        From Date
                    </label>
                    <div class="mt-1.5 relative">
                        <input 
                            type="date" 
                            wire:model.live="startDate"
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/50 px-3.5 py-2 text-sm font-medium text-gray-900 shadow-sm transition focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-800/60 dark:text-white dark:focus:bg-gray-800"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        To Date
                    </label>
                    <div class="mt-1.5 relative">
                        <input 
                            type="date" 
                            wire:model.live="endDate"
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/50 px-3.5 py-2 text-sm font-medium text-gray-900 shadow-sm transition focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-800/60 dark:text-white dark:focus:bg-gray-800"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        Branch Location
                    </label>
                    <div class="mt-1.5 relative">
                        <select 
                            wire:model.live="branchId"
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/50 px-3.5 py-2 text-sm font-medium text-gray-900 shadow-sm transition focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-800/60 dark:text-white dark:focus:bg-gray-800"
                        >
                            <option value="">All Branches (Consolidated)</option>
                            @foreach($this->branches as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-end">
                    <button 
                        type="button"
                        wire:click="$refresh"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/20 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                        <span>Update Statement</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Executive KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Revenues --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Revenues</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                        ${{ number_format($totalRevenue, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Gross operating turnover
                </div>
            </div>

            {{-- Gross Profit --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Gross Profit</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight {{ $grossProfit >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                        ${{ number_format($grossProfit, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Revenue less Cost of Sales
                </div>
            </div>

            {{-- Operating Expenses --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Operating Expenses</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                        <x-filament::icon icon="heroicon-o-credit-card" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                        ${{ number_format($totalExpenses, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Overhead &amp; operating costs
                </div>
            </div>

            {{-- Net Profit / Loss --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Net Profit / (Loss)</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $isProfit ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                        <x-filament::icon icon="{{ $isProfit ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-circle' }}" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight {{ $isProfit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        ${{ number_format($netIncome, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium {{ $isProfit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ $isProfit ? 'Net Income (Profit)' : 'Net Operating Loss' }}
                </div>
            </div>
        </div>

        {{-- Financial Statement Breakdown Document --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-6 md:p-8 space-y-8">

                {{-- 1. OPERATING REVENUES --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-500/10 text-xs font-black text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">1</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                Operating Revenues
                            </h3>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Amount (USD)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    <th class="py-2 pl-4 font-semibold w-32">Account Code</th>
                                    <th class="py-2 font-semibold">Account Title</th>
                                    <th class="py-2 pr-4 text-right font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                @forelse($operatingRevenues as $rev)
                                    @php
                                        $revObj = is_array($rev) ? (object) $rev : $rev;
                                        $code = $revObj->account_number ?? $revObj->number ?? '';
                                        $name = $revObj->name ?? $revObj->account_name ?? 'Revenue Account';
                                        $bal = (float) ($revObj->balance ?? $revObj->net_balance ?? 0);
                                    @endphp
                                    <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                        <td class="py-2.5 pl-4 font-mono text-xs text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ $code ? '#' . $code : '—' }}
                                        </td>
                                        <td class="py-2.5 font-medium text-gray-800 dark:text-gray-200">
                                            {{ $name }}
                                        </td>
                                        <td class="py-2.5 pr-4 text-right font-mono text-sm font-semibold text-gray-900 dark:text-white">
                                            ${{ number_format($bal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-xs italic text-gray-400">
                                            No operating revenue accounts found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 bg-gray-50/60 font-bold text-gray-900 dark:border-gray-800 dark:bg-gray-800/40 dark:text-white">
                                    <td colspan="2" class="py-3 pl-4 text-xs uppercase tracking-wider">Total Revenues</td>
                                    <td class="py-3 pr-4 text-right font-mono text-base font-black text-emerald-600 dark:text-emerald-400">
                                        ${{ number_format($totalRevenue, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- 2. COST OF GOODS SOLD (COGS) --}}
                @if(count($cogsAccounts) > 0 || $totalCogs > 0)
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-500/10 text-xs font-black text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">2</span>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                    Cost of Goods Sold (COGS)
                                </h3>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Amount (USD)</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                        <th class="py-2 pl-4 font-semibold w-32">Account Code</th>
                                        <th class="py-2 font-semibold">Account Title</th>
                                        <th class="py-2 pr-4 text-right font-semibold">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    @foreach($cogsAccounts as $cg)
                                        @php
                                            $cgObj = is_array($cg) ? (object) $cg : $cg;
                                            $code = $cgObj->account_number ?? $cgObj->number ?? '';
                                            $name = $cgObj->name ?? $cgObj->account_name ?? 'COGS Account';
                                            $bal = (float) ($cgObj->balance ?? $cgObj->net_balance ?? 0);
                                        @endphp
                                        <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                            <td class="py-2.5 pl-4 font-mono text-xs text-primary-600 dark:text-primary-400 font-semibold">
                                                {{ $code ? '#' . $code : '—' }}
                                            </td>
                                            <td class="py-2.5 font-medium text-gray-800 dark:text-gray-200">
                                                {{ $name }}
                                            </td>
                                            <td class="py-2.5 pr-4 text-right font-mono text-sm font-semibold text-rose-600 dark:text-rose-400">
                                                ({{ number_format($bal, 2) }})
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-gray-200 bg-gray-50/60 font-bold text-gray-900 dark:border-gray-800 dark:bg-gray-800/40 dark:text-white">
                                        <td colspan="2" class="py-3 pl-4 text-xs uppercase tracking-wider">Total Cost of Goods Sold</td>
                                        <td class="py-3 pr-4 text-right font-mono text-base font-black text-rose-600 dark:text-rose-400">
                                            ({{ number_format($totalCogs, 2) }})
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Gross Profit Ribbon --}}
                <div class="rounded-xl bg-gradient-to-r from-slate-100 via-indigo-50/40 to-slate-100 p-4 ring-1 ring-slate-200 dark:from-gray-800/70 dark:via-indigo-950/20 dark:to-gray-800/70 dark:ring-white/10">
                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            <span class="text-sm font-black uppercase tracking-wider text-gray-900 dark:text-white">Gross Profit Margin</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">Gross Profit:</span>
                            <span class="font-mono text-xl font-black {{ $grossProfit >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                ${{ number_format($grossProfit, 2) }} USD
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 3. OPERATING EXPENSES --}}
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-rose-500/10 text-xs font-black text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">3</span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                Operating Expenses
                            </h3>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Amount (USD)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    <th class="py-2 pl-4 font-semibold w-32">Account Code</th>
                                    <th class="py-2 font-semibold">Account Title</th>
                                    <th class="py-2 pr-4 text-right font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                @forelse($operatingExpenses as $exp)
                                    @php
                                        $expObj = is_array($exp) ? (object) $exp : $exp;
                                        $code = $expObj->account_number ?? $expObj->number ?? '';
                                        $name = $expObj->name ?? $expObj->account_name ?? 'Expense Account';
                                        $bal = (float) ($expObj->balance ?? $expObj->net_balance ?? 0);
                                    @endphp
                                    <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                        <td class="py-2.5 pl-4 font-mono text-xs text-primary-600 dark:text-primary-400 font-semibold">
                                            {{ $code ? '#' . $code : '—' }}
                                        </td>
                                        <td class="py-2.5 font-medium text-gray-800 dark:text-gray-200">
                                            {{ $name }}
                                        </td>
                                        <td class="py-2.5 pr-4 text-right font-mono text-sm font-semibold text-rose-600 dark:text-rose-400">
                                            ({{ number_format($bal, 2) }})
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-xs italic text-gray-400">
                                            No operating expense accounts found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 bg-gray-50/60 font-bold text-gray-900 dark:border-gray-800 dark:bg-gray-800/40 dark:text-white">
                                    <td colspan="2" class="py-3 pl-4 text-xs uppercase tracking-wider">Total Operating Expenses</td>
                                    <td class="py-3 pr-4 text-right font-mono text-base font-black text-rose-600 dark:text-rose-400">
                                        ({{ number_format($totalExpenses, 2) }})
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Grand Finale Net Income Card --}}
                <div class="overflow-hidden rounded-2xl border-2 {{ $isProfit ? 'border-emerald-500/40 bg-gradient-to-br from-emerald-50/60 via-emerald-100/30 to-white dark:border-emerald-500/30 dark:from-emerald-950/40 dark:via-gray-900 dark:to-gray-900' : 'border-rose-500/40 bg-gradient-to-br from-rose-50/60 via-rose-100/30 to-white dark:border-rose-500/30 dark:from-rose-950/40 dark:via-gray-900 dark:to-gray-900' }} p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-3 w-3 rounded-full {{ $isProfit ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                <span class="text-xs font-black uppercase tracking-wider {{ $isProfit ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300' }}">
                                    FINAL PERFORMANCE SUMMARY
                                </span>
                            </div>
                            <h2 class="mt-1 text-2xl font-black tracking-tight {{ $isProfit ? 'text-emerald-900 dark:text-emerald-200' : 'text-rose-900 dark:text-rose-200' }}">
                                {{ $isProfit ? 'Net Income (Profit)' : 'Net Loss' }}
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Consolidated statement result for period ending {{ $endDateFormatted }}
                            </p>
                        </div>

                        <div class="text-right">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Net Result
                            </div>
                            <div class="mt-1 inline-block border-b-4 border-double {{ $isProfit ? 'border-emerald-600 dark:border-emerald-400' : 'border-rose-600 dark:border-rose-400' }} pb-1">
                                <span class="font-mono text-3xl font-black {{ $isProfit ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                                    ${{ number_format($netIncome, 2) }}
                                </span>
                                <span class="text-xs font-bold {{ $isProfit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">USD</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-filament-panels::page>

