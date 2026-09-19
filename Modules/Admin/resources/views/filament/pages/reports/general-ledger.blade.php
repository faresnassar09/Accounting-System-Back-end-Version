<x-filament-panels::page>
    @php
        $reportData = $this->getReportData();
        $accountInfo = (object) ($reportData['account_info'] ?? []);
        $openingBalance = (float) ($reportData['opening_balance'] ?? 0);
        $closingBalance = (float) ($reportData['closing_balance'] ?? 0);
        $totalDebit = (float) ($reportData['total_debit'] ?? 0);
        $totalCredit = (float) ($reportData['total_credit'] ?? 0);
        $netMovement = $totalDebit - $totalCredit;
        $transactions = $reportData['transactions'] ?? [];
        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
    @endphp

    <div class="space-y-6">
        {{-- Executive Report Header Banner --}}
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
                        GENERAL LEDGER
                    </h1>
                    <p class="mt-1 text-xs text-slate-300">
                        @if(!empty($accountInfo->name))
                            Account: <span class="font-bold text-white">{{ $accountInfo->name }} ({{ !empty($accountInfo->number) ? '#' . $accountInfo->number : 'N/A' }})</span> &bull;
                        @endif
                        Period: <span class="font-semibold text-white">{{ $this->startDate }} &rarr; {{ $this->endDate }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Currency</div>
                        <div class="text-sm font-black text-white">USD ($)</div>
                    </div>
                    <div class="rounded-xl bg-white/5 px-4 py-2.5 backdrop-blur ring-1 ring-white/10 text-right">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Closing Balance</div>
                        <div class="text-sm font-black text-emerald-400 font-mono">
                            ${{ number_format($closingBalance, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        Target Account
                    </label>
                    <div class="mt-1.5 relative">
                        <select 
                            wire:model.live="accountId"
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/50 px-3.5 py-2 text-sm font-medium text-gray-900 shadow-sm transition focus:border-primary-500 focus:bg-white focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-800/60 dark:text-white dark:focus:bg-gray-800"
                        >
                            <option value="">Select an account...</option>
                            @foreach($this->accounts as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

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
            </div>
        </div>

        @if($reportData)
            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                {{-- Account Profile --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Account Card</span>
                    <div class="mt-2 text-base font-bold text-gray-900 dark:text-white truncate">
                        {{ $accountInfo->name ?? '—' }}
                    </div>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">
                            {{ !empty($accountInfo->number) ? '#' . $accountInfo->number : '' }}
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ ucwords(str_replace('_', ' ', $accountInfo->account_type ?? 'Standard')) }}
                        </span>
                    </div>
                </div>

                {{-- Opening Balance --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Opening Balance</span>
                    <div class="mt-2 text-2xl font-black tracking-tight text-gray-900 dark:text-white font-mono">
                        ${{ number_format($openingBalance, 2) }}
                    </div>
                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        As of {{ $this->startDate }}
                    </div>
                </div>

                {{-- Total Debits --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Period Debits</span>
                    <div class="mt-2 text-2xl font-black tracking-tight text-indigo-600 dark:text-indigo-400 font-mono">
                        ${{ number_format($totalDebit, 2) }}
                    </div>
                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        Inflows & additions
                    </div>
                </div>

                {{-- Total Credits --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Period Credits</span>
                    <div class="mt-2 text-2xl font-black tracking-tight text-violet-600 dark:text-violet-400 font-mono">
                        ${{ number_format($totalCredit, 2) }}
                    </div>
                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        Outflows & deductions
                    </div>
                </div>

                {{-- Closing Balance --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-primary-500">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Closing Balance</span>
                    <div class="mt-2 text-2xl font-black tracking-tight text-primary-600 dark:text-primary-400 font-mono">
                        ${{ number_format($closingBalance, 2) }}
                    </div>
                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                        Ending position as of {{ $this->endDate }}
                    </div>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/40">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Transaction History & Running Ledger</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Chronological breakdown of postings with cumulative running balance</p>
                        </div>
                        <span class="rounded-md bg-gray-200/60 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ count($transactions) }} Postings
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200/80 bg-gray-50/80 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                                <th scope="col" class="py-3.5 pl-6 pr-3">Posting Date</th>
                                <th scope="col" class="px-3 py-3.5">Reference</th>
                                <th scope="col" class="px-3 py-3.5">Description</th>
                                <th scope="col" class="px-3 py-3.5">Branch</th>
                                <th scope="col" class="px-4 py-3.5 text-right">Debit (USD)</th>
                                <th scope="col" class="px-4 py-3.5 text-right">Credit (USD)</th>
                                <th scope="col" class="py-3.5 pl-4 pr-6 text-right">Running Balance (USD)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                            {{-- Opening balance row --}}
                            <tr class="bg-amber-50/40 dark:bg-amber-950/10 font-medium">
                                <td class="py-3 pl-6 pr-3 font-mono text-xs text-gray-500">
                                    {{ $this->startDate }}
                                </td>
                                <td class="px-3 py-3 font-mono text-xs font-bold text-amber-700 dark:text-amber-400" colspan="3">
                                    &bull; OPENING CARRIED-FORWARD BALANCE
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-gray-400">—</td>
                                <td class="px-4 py-3 text-right font-mono text-xs text-gray-400">—</td>
                                <td class="py-3 pl-4 pr-6 text-right font-mono text-xs font-black text-gray-900 dark:text-white">
                                    ${{ number_format($openingBalance, 2) }}
                                </td>
                            </tr>

                            @forelse($transactions as $txn)
                                @php
                                    $txnObj = (object) $txn;
                                    $txnDate = !empty($txnObj->date) ? \Carbon\Carbon::parse($txnObj->date)->format('Y-m-d') : '—';
                                    $txnRef = $txnObj->reference ?? $txnObj->entry_reference ?? '—';
                                    $txnDesc = $txnObj->description ?? '—';
                                    $txnBranch = $txnObj->branch_name ?? '—';
                                    $txnDebit = (float) ($txnObj->debit ?? 0);
                                    $txnCredit = (float) ($txnObj->credit ?? 0);
                                    $txnRunBal = (float) ($txnObj->running_balance ?? 0);
                                @endphp
                                <tr class="transition hover:bg-slate-50/80 dark:hover:bg-gray-800/40">
                                    <td class="py-3.5 pl-6 pr-3 font-mono text-xs text-gray-600 dark:text-gray-300">
                                        {{ $txnDate }}
                                    </td>
                                    <td class="px-3 py-3.5 font-mono text-xs font-bold text-primary-600 dark:text-primary-400">
                                        {{ $txnRef }}
                                    </td>
                                    <td class="px-3 py-3.5 text-gray-900 dark:text-white">
                                        {{ $txnDesc }}
                                    </td>
                                    <td class="px-3 py-3.5 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            {{ $txnBranch }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums text-gray-900 dark:text-white">
                                        {{ $txnDebit > 0 ? number_format($txnDebit, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono tabular-nums text-gray-900 dark:text-white">
                                        {{ $txnCredit > 0 ? number_format($txnCredit, 2) : '—' }}
                                    </td>
                                    <td class="py-3.5 pl-4 pr-6 text-right font-mono tabular-nums font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($txnRunBal, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                        No transactions recorded for this account in the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-900 bg-slate-50 font-black text-gray-900 dark:border-white dark:bg-gray-800/80 dark:text-white">
                                <td colspan="4" class="py-4 pl-6 text-xs uppercase tracking-wider">
                                    Period Activity Totals / Closing Balance
                                </td>
                                <td class="px-4 py-4 text-right font-mono text-base tabular-nums border-b-4 border-double border-gray-900 dark:border-white">
                                    ${{ number_format($totalDebit, 2) }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono text-base tabular-nums border-b-4 border-double border-gray-900 dark:border-white">
                                    ${{ number_format($totalCredit, 2) }}
                                </td>
                                <td class="py-4 pl-4 pr-6 text-right font-mono text-base tabular-nums font-black text-primary-600 dark:text-primary-400 border-b-4 border-double border-gray-900 dark:border-white">
                                    ${{ number_format($closingBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <x-filament::icon icon="heroicon-o-book-open" class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">No Account Selected</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please choose an account from the filter dropdown above to render the General Ledger.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
