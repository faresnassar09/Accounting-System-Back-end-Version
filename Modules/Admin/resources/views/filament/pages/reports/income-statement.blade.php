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
    @endphp

    {{-- Filter Bar --}}
    <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
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

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Revenues</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalRevenue, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Gross Profit</div>
            <div class="mt-2 text-2xl font-bold {{ $grossProfit >= 0 ? 'text-gray-900 dark:text-white' : 'text-danger-600' }}">
                {{ number_format($grossProfit, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Operating Expenses</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalExpenses, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>

        <div class="p-4 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net Profit / (Loss)</div>
            <div class="mt-2 text-2xl font-bold {{ $isProfit ? 'text-green-600 dark:text-green-400' : 'text-danger-600 dark:text-danger-400' }}">
                {{ number_format($netIncome, 2) }} <span class="text-xs text-gray-400">USD</span>
            </div>
        </div>
    </div>

    {{-- Statement Breakdown --}}
    <div class="overflow-hidden bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6 divide-y divide-gray-200 dark:divide-gray-800 space-y-6">

            {{-- 1. Revenues --}}
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">
                    1. Operating Revenues
                </h3>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        @forelse($operatingRevenues as $rev)
                            @php $revObj = (object) $rev; @endphp
                            <tr>
                                <td class="py-2 font-mono text-xs text-gray-500 w-24">{{ !empty($revObj->number) ? '#' . $revObj->number : '' }}</td>
                                <td class="py-2 text-gray-800 dark:text-gray-200">{{ $revObj->name }}</td>
                                <td class="py-2 text-right font-mono text-gray-900 dark:text-white">{{ number_format($revObj->balance ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-2 text-sm text-gray-400 italic">No operating revenue transactions recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700">
                            <td colspan="2" class="pt-3">Total Operating Revenues</td>
                            <td class="pt-3 text-right font-mono text-base">{{ number_format($totalRevenue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 2. Cost of Goods Sold --}}
            @if(count($cogsAccounts) > 0 || $totalCogs > 0)
                <div class="pt-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">
                        2. Cost of Goods Sold (COGS)
                    </h3>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                            @foreach($cogsAccounts as $cg)
                                @php $cgObj = (object) $cg; @endphp
                                <tr>
                                    <td class="py-2 font-mono text-xs text-gray-500 w-24">{{ !empty($cgObj->number) ? '#' . $cgObj->number : '' }}</td>
                                    <td class="py-2 text-gray-800 dark:text-gray-200">{{ $cgObj->name }}</td>
                                    <td class="py-2 text-right font-mono text-gray-900 dark:text-white">{{ number_format($cgObj->balance ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700">
                                <td colspan="2" class="pt-3">Total Cost of Goods Sold</td>
                                <td class="pt-3 text-right font-mono text-base text-danger-600">({{ number_format($totalCogs, 2) }})</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            {{-- Gross Profit Banner --}}
            <div class="pt-4 pb-2">
                <div class="flex justify-between items-center px-4 py-3 bg-gray-50 dark:bg-gray-800/60 rounded-lg">
                    <span class="font-bold text-gray-900 dark:text-white uppercase tracking-wide text-sm">Gross Profit</span>
                    <span class="font-bold font-mono text-lg {{ $grossProfit >= 0 ? 'text-gray-900 dark:text-white' : 'text-danger-600' }}">
                        {{ number_format($grossProfit, 2) }} USD
                    </span>
                </div>
            </div>

            {{-- 3. Operating Expenses --}}
            <div class="pt-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">
                    3. Operating Expenses
                </h3>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        @forelse($operatingExpenses as $exp)
                            @php $expObj = (object) $exp; @endphp
                            <tr>
                                <td class="py-2 font-mono text-xs text-gray-500 w-24">{{ !empty($expObj->number) ? '#' . $expObj->number : '' }}</td>
                                <td class="py-2 text-gray-800 dark:text-gray-200">{{ $expObj->name }}</td>
                                <td class="py-2 text-right font-mono text-gray-900 dark:text-white">{{ number_format($expObj->balance ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-2 text-sm text-gray-400 italic">No operating expense transactions recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700">
                            <td colspan="2" class="pt-3">Total Operating Expenses</td>
                            <td class="pt-3 text-right font-mono text-base text-danger-600">({{ number_format($totalExpenses, 2) }})</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Net Income Final Summary Banner --}}
            <div class="pt-6">
                <div class="flex justify-between items-center p-5 rounded-xl {{ $isProfit ? 'bg-green-50 border border-green-200 dark:bg-green-950/20 dark:border-green-800' : 'bg-red-50 border border-red-200 dark:bg-red-950/20 dark:border-red-800' }}">
                    <div>
                        <div class="text-xs uppercase font-bold tracking-wider {{ $isProfit ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                            {{ $isProfit ? 'Net Income (Profit)' : 'Net Loss' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Period: {{ $this->startDate }} to {{ $this->endDate }}
                        </div>
                    </div>
                    <div class="text-2xl font-black font-mono {{ $isProfit ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                        {{ number_format($netIncome, 2) }} USD
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page>
