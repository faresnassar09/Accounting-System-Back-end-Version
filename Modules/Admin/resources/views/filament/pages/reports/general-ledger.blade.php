<x-filament-panels::page>
    @php
        $reportData = $this->getReportData();
        $accountInfo = (object) ($reportData['account_info'] ?? []);
        $openingBalance = (float) ($reportData['opening_balance'] ?? 0);
        $closingBalance = (float) ($reportData['closing_balance'] ?? 0);
        $totalDebit = (float) ($reportData['total_debit'] ?? 0);
        $totalCredit = (float) ($reportData['total_credit'] ?? 0);
        $transactions = $reportData['transactions'] ?? [];
    @endphp

    {{-- Filter Bar --}}
    <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Account</label>
                <select 
                    wire:model.live="accountId"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                >
                    <option value="">Select Account...</option>
                    @foreach($this->accounts as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">From Date</label>
                <input 
                    type="date" 
                    wire:model.live="startDate"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">To Date</label>
                <input 
                    type="date" 
                    wire:model.live="endDate"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Branch</label>
                <select 
                    wire:model.live="branchId"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                >
                    <option value="">All Branches</option>
                    @foreach($this->branches as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($reportData)
        {{-- Account Meta Card & Summary KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Account Details</div>
                <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                    {{ $accountInfo->name ?? '—' }}
                </div>
                <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
                    {{ !empty($accountInfo->number) ? '#' . $accountInfo->number : '' }} &bull; {{ ucwords(str_replace('_', ' ', $accountInfo->account_type ?? '')) }}
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Opening Balance</div>
                <div class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($openingBalance, 2) }} <span class="text-xs text-gray-400">USD</span>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Period Debits</div>
                <div class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($totalDebit, 2) }} <span class="text-xs text-gray-400">USD</span>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Period Credits</div>
                <div class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($totalCredit, 2) }} <span class="text-xs text-gray-400">USD</span>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Closing Balance</div>
                <div class="mt-2 text-xl font-bold text-primary-600 dark:text-primary-400">
                    {{ number_format($closingBalance, 2) }} <span class="text-xs text-gray-400">USD</span>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="overflow-hidden bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Date</th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Reference</th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Description</th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Branch</th>
                            <th class="px-4 py-3 font-semibold text-right text-gray-900 dark:text-white">Debit (USD)</th>
                            <th class="px-4 py-3 font-semibold text-right text-gray-900 dark:text-white">Credit (USD)</th>
                            <th class="px-4 py-3 font-semibold text-right text-gray-900 dark:text-white">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        {{-- Opening balance row --}}
                        <tr class="bg-gray-50/70 dark:bg-gray-800/20 italic">
                            <td class="px-4 py-2 font-mono text-xs text-gray-500">
                                {{ $this->startDate }}
                            </td>
                            <td class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300" colspan="3">
                                [OPENING BALANCE]
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-400">—</td>
                            <td class="px-4 py-2 text-right font-mono text-xs text-gray-400">—</td>
                            <td class="px-4 py-2 text-right font-mono text-xs font-bold text-gray-900 dark:text-white">
                                {{ number_format($openingBalance, 2) }}
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
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                    {{ $txnDate }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">
                                    {{ $txnRef }}
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $txnDesc }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $txnBranch }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                    {{ $txnDebit > 0 ? number_format($txnDebit, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                    {{ $txnCredit > 0 ? number_format($txnCredit, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-medium text-gray-900 dark:text-white">
                                    {{ number_format($txnRunBal, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No transactions recorded for this account in the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold dark:bg-gray-800">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-gray-900 dark:text-white uppercase tracking-wider text-xs">
                                Period Totals / Closing Balance
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                {{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                {{ number_format($totalCredit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-primary-600 dark:text-primary-400">
                                {{ number_format($closingBalance, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="p-8 text-center bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-gray-500">
            Please select an account to generate the General Ledger report.
        </div>
    @endif
</x-filament-panels::page>
