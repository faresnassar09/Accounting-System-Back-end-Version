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

    {{-- KPI Equation Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Assets</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalAssets, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Liabilities & Equity</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalLiabEquity, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Difference</div>
            <div class="mt-2 text-2xl font-bold {{ $difference == 0 ? 'text-gray-900 dark:text-white' : 'text-danger-600' }}">
                {{ number_format($difference, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Accounting Equation</div>
            <div class="mt-2">
                @if($isBalanced)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 mr-1" /> Balanced (A = L + E)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-4 h-4 mr-1" /> Equation Out of Balance
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Detailed Statement Sections --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- LEFT COLUMN: ASSETS --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
                    Assets
                </h3>

                <div class="space-y-6">
                    @forelse($assetSubTypes as $subKey => $subType)
                        @php
                            $subTypeName = is_array($subType) ? ($subType['type_name'] ?? ucwords(str_replace('_', ' ', $subKey))) : ($subType->type_name ?? ucwords(str_replace('_', ' ', $subKey)));
                            $subTypeTotal = is_array($subType) ? ($subType['type_total'] ?? 0) : ($subType->type_total ?? 0);
                            $accounts = is_array($subType) ? ($subType['accounts'] ?? []) : ($subType->accounts ?? []);
                        @endphp
                        <div>
                            <div class="flex justify-between items-center text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 rounded">
                                <span>{{ $subTypeName }}</span>
                                <span class="font-mono">{{ number_format($subTypeTotal, 2) }}</span>
                            </div>

                            <table class="w-full text-sm mt-2">
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    @forelse($accounts as $acc)
                                        @php
                                            $accObj = (object) $acc;
                                            $accName = $accObj->name ?? 'Account';
                                            $accNumber = $accObj->number ?? '';
                                            $accBal = (float) ($accObj->netBalance ?? $accObj->netbalance ?? $accObj->balance ?? 0);
                                        @endphp
                                        <tr>
                                            <td class="py-1.5 pl-3 font-mono text-xs text-gray-500 w-20">
                                                {{ !empty($accNumber) ? '#' . $accNumber : '' }}
                                            </td>
                                            <td class="py-1.5 text-gray-700 dark:text-gray-300">{{ $accName }}</td>
                                            <td class="py-1.5 pr-3 text-right font-mono text-gray-900 dark:text-white">
                                                {{ number_format($accBal, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-1 pl-3 text-xs text-gray-400 italic">No accounts with balances.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 italic py-4">No asset accounts recorded.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 pt-4 border-t-2 border-gray-900 dark:border-white flex justify-between items-center font-black text-gray-900 dark:text-white text-base">
                <span>TOTAL ASSETS</span>
                <span class="font-mono text-xl">{{ number_format($totalAssets, 2) }} USD</span>
            </div>
        </div>

        {{-- RIGHT COLUMN: LIABILITIES & EQUITY --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
                    Liabilities & Owner Equity
                </h3>

                <div class="space-y-6">
                    @forelse($liabEquitySubTypes as $subKey => $subType)
                        @php
                            $subTypeName = is_array($subType) ? ($subType['type_name'] ?? ucwords(str_replace('_', ' ', $subKey))) : ($subType->type_name ?? ucwords(str_replace('_', ' ', $subKey)));
                            $subTypeTotal = is_array($subType) ? ($subType['type_total'] ?? 0) : ($subType->type_total ?? 0);
                            $accounts = is_array($subType) ? ($subType['accounts'] ?? []) : ($subType->accounts ?? []);
                        @endphp
                        <div>
                            <div class="flex justify-between items-center text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 rounded">
                                <span>{{ $subTypeName }}</span>
                                <span class="font-mono">{{ number_format($subTypeTotal, 2) }}</span>
                            </div>

                            <table class="w-full text-sm mt-2">
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    @forelse($accounts as $acc)
                                        @php
                                            $accObj = (object) $acc;
                                            $accName = $accObj->name ?? 'Account';
                                            $accNumber = $accObj->number ?? '';
                                            $accBal = (float) ($accObj->netBalance ?? $accObj->netbalance ?? $accObj->balance ?? 0);
                                        @endphp
                                        <tr>
                                            <td class="py-1.5 pl-3 font-mono text-xs text-gray-500 w-20">
                                                {{ !empty($accNumber) ? '#' . $accNumber : '' }}
                                            </td>
                                            <td class="py-1.5 text-gray-700 dark:text-gray-300">{{ $accName }}</td>
                                            <td class="py-1.5 pr-3 text-right font-mono text-gray-900 dark:text-white">
                                                {{ number_format($accBal, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-1 pl-3 text-xs text-gray-400 italic">No accounts with balances.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 italic py-4">No liabilities or equity recorded.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 pt-4 border-t-2 border-gray-900 dark:border-white flex justify-between items-center font-black text-gray-900 dark:text-white text-base">
                <span>TOTAL LIABILITIES & EQUITY</span>
                <span class="font-mono text-xl">{{ number_format($totalLiabEquity, 2) }} USD</span>
            </div>
        </div>

    </div>
</x-filament-panels::page>
