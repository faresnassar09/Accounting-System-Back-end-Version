<x-filament-panels::page>
    @php
        $reportData = $this->getReportData();
        $accounts = $reportData['accounts'] ?? [];
        $totals = $reportData['totals'] ?? [];
        $totalDebit = (float) ($totals['total_debit'] ?? 0);
        $totalCredit = (float) ($totals['total_credit'] ?? 0);
        $isBalanced = (bool) ($totals['isBalanced'] ?? ($totalDebit === $totalCredit));
        $difference = abs($totalDebit - $totalCredit);
        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
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
                        TRIAL BALANCE
                    </h1>
                    <p class="mt-1 text-xs text-slate-300">
                        Financial ledger balances as of <span class="font-semibold text-white">{{ $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('F d, Y') : now()->format('F d, Y') }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Currency</div>
                        <div class="text-sm font-black text-white">USD ($)</div>
                    </div>
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ledger Status</div>
                        @if($isBalanced)
                            <div class="inline-flex items-center gap-1 text-sm font-black text-emerald-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Balanced
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1 text-sm font-black text-rose-400">
                                <span class="h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                                Unbalanced
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

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Debits --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Debits</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <x-filament::icon icon="heroicon-o-arrow-down-left" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                        ${{ number_format($totalDebit, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Cumulative debit movements
                </div>
            </div>

            {{-- Total Credits --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Credits</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400">
                        <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                        ${{ number_format($totalCredit, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    Cumulative credit movements
                </div>
            </div>

            {{-- Balance Difference --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Difference</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $difference == 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                        <x-filament::icon icon="heroicon-o-scale" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-black tracking-tight {{ $difference == 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                        ${{ number_format($difference, 2) }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">USD</span>
                </div>
                <div class="mt-2 text-[11px] font-medium {{ $difference == 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-rose-600 dark:text-rose-400 font-semibold' }}">
                    {{ $difference == 0 ? 'Exact debit & credit equilibrium' : 'Investigate posting discrepancies' }}
                </div>
            </div>

            {{-- Ledger Status --}}
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Verification</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $isBalanced ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' }}">
                        <x-filament::icon icon="{{ $isBalanced ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-triangle' }}" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    @if($isBalanced)
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Balanced
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-3 py-1 text-sm font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-400">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            Out of Balance
                        </span>
                    @endif
                </div>
                <div class="mt-2 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                    GAAP/IFRS double-entry integrity
                </div>
            </div>
        </div>

        {{-- Detailed Data Table --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/40">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Chart of Accounts Breakdown</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Summary of period activity and ending balances by account</p>
                    </div>
                    <span class="rounded-md bg-gray-200/60 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ count($accounts) }} Accounts
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200/80 bg-gray-50/80 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                            <th scope="col" class="py-3.5 pl-6 pr-3">Account Code</th>
                            <th scope="col" class="px-3 py-3.5">Account Title</th>
                            <th scope="col" class="px-3 py-3.5">Classification</th>
                            <th scope="col" class="px-4 py-3.5 text-right">Debit Balance (USD)</th>
                            <th scope="col" class="py-3.5 pl-4 pr-6 text-right">Credit Balance (USD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        @forelse($accounts as $acc)
                            @php
                                $accObj = (object) $acc;
                                $accDebit = (float) ($accObj->debit ?? 0);
                                $accCredit = (float) ($accObj->credit ?? 0);
                            @endphp
                            <tr class="transition hover:bg-slate-50/80 dark:hover:bg-gray-800/40">
                                <td class="py-3.5 pl-6 pr-3 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">
                                    {{ $accObj->number ?? '—' }}
                                </td>
                                <td class="px-3 py-3.5 font-medium text-gray-900 dark:text-white">
                                    {{ $accObj->name }}
                                </td>
                                <td class="px-3 py-3.5 text-xs">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        {{ ucwords(str_replace('_', ' ', $accObj->account_type ?? '')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums text-gray-900 dark:text-white">
                                    {{ $accDebit > 0 ? number_format($accDebit, 2) : '—' }}
                                </td>
                                <td class="py-3.5 pl-4 pr-6 text-right font-mono tabular-nums text-gray-900 dark:text-white">
                                    {{ $accCredit > 0 ? number_format($accCredit, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No accounts or transactions recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-900 bg-slate-50 font-black text-gray-900 dark:border-white dark:bg-gray-800/80 dark:text-white">
                            <td colspan="3" class="py-4 pl-6 text-xs uppercase tracking-wider">
                                Total Statement Balances
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-base tabular-nums border-b-4 border-double border-gray-900 dark:border-white">
                                ${{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="py-4 pl-4 pr-6 text-right font-mono text-base tabular-nums border-b-4 border-double border-gray-900 dark:border-white">
                                ${{ number_format($totalCredit, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
