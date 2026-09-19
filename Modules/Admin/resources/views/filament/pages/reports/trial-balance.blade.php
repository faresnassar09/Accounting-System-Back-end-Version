<x-filament-panels::page>
    @php
        $reportData = $this->getReportData();
        $accounts = $reportData['accounts'] ?? [];
        $totals = $reportData['totals'] ?? [];
        $totalDebit = (float) ($totals['total_debit'] ?? 0);
        $totalCredit = (float) ($totals['total_credit'] ?? 0);
        $isBalanced = (bool) ($totals['isBalanced'] ?? ($totalDebit === $totalCredit));
        $difference = abs($totalDebit - $totalCredit);
    @endphp

    {{-- Filter Bar --}}
    <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">As of Date</label>
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

            <div class="flex items-end">
                <button 
                    type="button"
                    wire:click="$refresh"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-sm"
                >
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4 mr-2" />
                    Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Debits</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalDebit, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Credits</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalCredit, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Difference</div>
            <div class="mt-2 text-2xl font-bold {{ $difference == 0 ? 'text-gray-900 dark:text-white' : 'text-danger-600' }}">
                {{ number_format($difference, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</div>
            <div class="mt-2">
                @if($isBalanced)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 mr-1" /> Balanced
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-4 h-4 mr-1" /> Unbalanced
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Trial Balance Table --}}
    <div class="overflow-hidden bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Account Code</th>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Account Name</th>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Classification</th>
                        <th class="px-4 py-3 font-semibold text-right text-gray-900 dark:text-white">Debit (USD)</th>
                        <th class="px-4 py-3 font-semibold text-right text-gray-900 dark:text-white">Credit (USD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ $acc['number'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $acc['name'] }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ ucwords(str_replace('_', ' ', $acc['account_type'] ?? '')) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                {{ (float) ($acc['debit'] ?? 0) > 0 ? number_format($acc['debit'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                                {{ (float) ($acc['credit'] ?? 0) > 0 ? number_format($acc['credit'], 2) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No accounts or transactions found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-bold dark:bg-gray-800">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-gray-900 dark:text-white uppercase tracking-wider text-xs">
                            Total
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                            {{ number_format($totalDebit, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                            {{ number_format($totalCredit, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>
