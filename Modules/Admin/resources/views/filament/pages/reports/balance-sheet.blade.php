<x-filament-panels::page>
    @php
        $reportData = $this->getReportData();
        $assetsGroup = $reportData['assets_group'] ?? [];
        $totalAssets = (float) ($assetsGroup['group_total'] ?? 0);
        $assetSubTypes = $assetsGroup['sub_types'] ?? [];

        $liabEquityGroup = $reportData['liabilities_and_equity_group'] ?? [];
        $totalLiabEquity = (float) ($liabEquityGroup['group_total'] ?? 0);
        $liabEquitySubTypes = $liabEquityGroup['sub_types'] ?? [];

        $summary = $reportData['balance_sheet_summary'] ?? [];
        $isBalanced = (bool) ($summary['balanced'] ?? ($totalAssets === $totalLiabEquity));
        $difference = abs($totalAssets - $totalLiabEquity);

        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
        $asOfFormatted = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('F d, Y') : now()->format('F d, Y');
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
                        BALANCE SHEET
                    </h1>
                    <p class="mt-1 text-xs text-slate-300">
                        Statement of Financial Position as of <span class="font-semibold text-white">{{ $asOfFormatted }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Currency</div>
                        <div class="text-sm font-black text-white">USD ($)</div>
                    </div>
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Equation Status</div>
                        @if($isBalanced)
                            <div class="inline-flex items-center gap-1.5 text-sm font-black text-emerald-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Balanced (A = L + E)
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 text-sm font-black text-rose-400">
                                <span class="h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                                Equation Out of Balance
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        As of Date
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

        {{-- KPI Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Assets --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Assets</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                        ${{ number_format($totalAssets, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Total economic resources
                </div>
            </div>

            {{-- Total Liabilities & Equity --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Liabilities &amp; Equity</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
                        <x-filament::icon icon="heroicon-o-scale" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                        ${{ number_format($totalLiabEquity, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Claims &amp; owner capital
                </div>
            </div>

            {{-- Equation Variance --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Difference</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $difference == 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                        <x-filament::icon icon="{{ $difference == 0 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle' }}" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight {{ $difference == 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                        ${{ number_format($difference, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    {{ $difference == 0 ? 'Perfect equilibrium' : 'Discrepancy detected' }}
                </div>
            </div>

            {{-- Accounting Equation Badge Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Accounting Equation</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    @if($isBalanced)
                        <span class="inline-flex items-center rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-500/30">
                            <x-filament::icon icon="heroicon-o-check-circle" class="mr-1.5 h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            Balanced (A = L + E)
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300 dark:ring-rose-500/30">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="mr-1.5 h-4 w-4 text-rose-600 dark:text-rose-400" />
                            Equation Out of Balance
                        </span>
                    @endif
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Fundamental IFRS validation
                </div>
            </div>
        </div>

        {{-- Detailed Classified Balance Sheet Sections (2-Column Grid) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- LEFT COLUMN: ASSETS --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                                <x-filament::icon icon="heroicon-o-building-office-2" class="h-4 w-4" />
                            </div>
                            <h2 class="text-base font-black uppercase tracking-wider text-gray-900 dark:text-white">
                                Assets
                            </h2>
                        </div>
                        <span class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">
                            ${{ number_format($totalAssets, 2) }}
                        </span>
                    </div>

                    <div class="mt-5 space-y-6">
                        @forelse($assetSubTypes as $subKey => $subType)
                            @php
                                $subTypeName = is_array($subType) ? ($subType['type_name'] ?? ucwords(str_replace('_', ' ', $subKey))) : ($subType->type_name ?? ucwords(str_replace('_', ' ', $subKey)));
                                $subTypeTotal = (float) (is_array($subType) ? ($subType['type_total'] ?? 0) : ($subType->type_total ?? 0));
                                $accounts = is_array($subType) ? ($subType['accounts'] ?? []) : ($subType->accounts ?? []);
                            @endphp
                            <div class="space-y-2">
                                <div class="flex items-center justify-between rounded-xl bg-gray-50/80 px-3.5 py-2 ring-1 ring-gray-950/5 dark:bg-gray-800/50 dark:ring-white/5">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                        {{ $subTypeName }}
                                    </span>
                                    <span class="font-mono text-xs font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($subTypeTotal, 2) }}
                                    </span>
                                </div>

                                <table class="w-full text-left text-sm">
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                        @forelse($accounts as $acc)
                                            @php
                                                $accObj = is_array($acc) ? (object) $acc : $acc;
                                                $accName = $accObj->name ?? $accObj->account_name ?? 'Account';
                                                $accNumber = $accObj->number ?? $accObj->account_number ?? '';
                                                $accBal = (float) ($accObj->netBalance ?? $accObj->netbalance ?? $accObj->balance ?? 0);
                                            @endphp
                                            <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                                <td class="py-2 pl-3 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400 w-24">
                                                    {{ !empty($accNumber) ? '#' . $accNumber : '—' }}
                                                </td>
                                                <td class="py-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $accName }}
                                                </td>
                                                <td class="py-2 pr-3 text-right font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                                    ${{ number_format($accBal, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="py-2 pl-3 text-xs italic text-gray-400">
                                                    No account balances in this category.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs italic text-gray-400">
                                No asset accounts recorded.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Left Bottom Total --}}
                <div class="border-t border-gray-100 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-800/60">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white">
                            Total Assets
                        </span>
                        <div class="inline-block border-b-4 border-double border-blue-600 pb-0.5 dark:border-blue-400">
                            <span class="font-mono text-xl font-black text-blue-700 dark:text-blue-300">
                                ${{ number_format($totalAssets, 2) }}
                            </span>
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400">USD</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: LIABILITIES & EQUITY --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                                <x-filament::icon icon="heroicon-o-scale" class="h-4 w-4" />
                            </div>
                            <h2 class="text-base font-black uppercase tracking-wider text-gray-900 dark:text-white">
                                Liabilities &amp; Owner Equity
                            </h2>
                        </div>
                        <span class="font-mono text-sm font-bold text-purple-600 dark:text-purple-400">
                            ${{ number_format($totalLiabEquity, 2) }}
                        </span>
                    </div>

                    <div class="mt-5 space-y-6">
                        @forelse($liabEquitySubTypes as $subKey => $subType)
                            @php
                                $subTypeName = is_array($subType) ? ($subType['type_name'] ?? ucwords(str_replace('_', ' ', $subKey))) : ($subType->type_name ?? ucwords(str_replace('_', ' ', $subKey)));
                                $subTypeTotal = (float) (is_array($subType) ? ($subType['type_total'] ?? 0) : ($subType->type_total ?? 0));
                                $accounts = is_array($subType) ? ($subType['accounts'] ?? []) : ($subType->accounts ?? []);
                            @endphp
                            <div class="space-y-2">
                                <div class="flex items-center justify-between rounded-xl bg-gray-50/80 px-3.5 py-2 ring-1 ring-gray-950/5 dark:bg-gray-800/50 dark:ring-white/5">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                        {{ $subTypeName }}
                                    </span>
                                    <span class="font-mono text-xs font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($subTypeTotal, 2) }}
                                    </span>
                                </div>

                                <table class="w-full text-left text-sm">
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                        @forelse($accounts as $acc)
                                            @php
                                                $accObj = is_array($acc) ? (object) $acc : $acc;
                                                $accName = $accObj->name ?? $accObj->account_name ?? 'Account';
                                                $accNumber = $accObj->number ?? $accObj->account_number ?? '';
                                                $accBal = (float) ($accObj->netBalance ?? $accObj->netbalance ?? $accObj->balance ?? 0);
                                            @endphp
                                            <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                                <td class="py-2 pl-3 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400 w-24">
                                                    {{ !empty($accNumber) ? '#' . $accNumber : '—' }}
                                                </td>
                                                <td class="py-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $accName }}
                                                </td>
                                                <td class="py-2 pr-3 text-right font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                                    ${{ number_format($accBal, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="py-2 pl-3 text-xs italic text-gray-400">
                                                    No account balances in this category.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs italic text-gray-400">
                                No liabilities or equity recorded.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right Bottom Total --}}
                <div class="border-t border-gray-100 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-800/60">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white">
                            Total Liabilities &amp; Equity
                        </span>
                        <div class="inline-block border-b-4 border-double border-purple-600 pb-0.5 dark:border-purple-400">
                            <span class="font-mono text-xl font-black text-purple-700 dark:text-purple-300">
                                ${{ number_format($totalLiabEquity, 2) }}
                            </span>
                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400">USD</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Accounting Equation Equilibrium Banner --}}
        <div class="rounded-2xl border-2 {{ $isBalanced ? 'border-emerald-500/40 bg-gradient-to-r from-emerald-50/50 via-white to-emerald-50/50 dark:border-emerald-500/30 dark:from-emerald-950/20 dark:via-gray-900 dark:to-emerald-950/20' : 'border-rose-500/40 bg-gradient-to-r from-rose-50/50 via-white to-rose-50/50 dark:border-rose-500/30 dark:from-rose-950/20 dark:via-gray-900 dark:to-rose-950/20' }} p-5">
            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $isBalanced ? 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400' }}">
                        <x-filament::icon icon="{{ $isBalanced ? 'heroicon-o-check-badge' : 'heroicon-o-shield-exclamation' }}" class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="text-xs font-black uppercase tracking-wider {{ $isBalanced ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300' }}">
                            {{ $isBalanced ? 'Double-Entry Equilibrium Verified' : 'Double-Entry Discrepancy Detected' }}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $isBalanced ? 'Total Assets strictly equal Total Liabilities plus Owner Equity.' : 'Assets do not equal Liabilities plus Equity. Check unposted journal adjustments.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 font-mono text-sm font-black">
                    <span class="text-blue-600 dark:text-blue-400">Assets ${{ number_format($totalAssets, 2) }}</span>
                    <span class="text-gray-400">=</span>
                    <span class="text-purple-600 dark:text-purple-400">Liab &amp; Eq ${{ number_format($totalLiabEquity, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

