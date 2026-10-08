<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

    @php
        $data = $this->getReportData();
        $netIncome = (float) ($data['net_income'] ?? 0);
        $depreciation = (float) ($data['depreciation'] ?? 0);
        $operatingAssets = $data['operating_asset_items'] ?? [];
        $operatingLiabs = $data['operating_liab_items'] ?? [];
        $netOperating = (float) ($data['net_operating_cash_flow'] ?? 0);

        $investingItems = $data['investing_items'] ?? [];
        $netInvesting = (float) ($data['net_investing_cash_flow'] ?? 0);

        $financingItems = $data['financing_items'] ?? [];
        $netFinancing = (float) ($data['net_financing_cash_flow'] ?? 0);

        $beginningCash = (float) ($data['beginning_cash'] ?? 0);
        $endingCash = (float) ($data['ending_cash'] ?? 0);
        $computedNetCash = (float) ($data['computed_net_cash_flow'] ?? 0);
        $isBalanced = (bool) ($data['is_balanced'] ?? false);
        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
    @endphp

    <div class="fr-wrapper">
        {{-- Executive Banner --}}
        <div class="fr-banner">
            <div>
                <div class="fr-banner-badges">
                    <span class="fr-tag">
                        {{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}
                    </span>
                    <span style="color: #94a3b8;">&bull;</span>
                    <span class="fr-tag" style="background: rgba(255,255,255,0.06);">
                        {{ $selectedBranch }}
                    </span>
                    <span style="color: #94a3b8;">&bull;</span>
                    <span class="fr-tag" style="background: rgba(255,255,255,0.06);">
                        GAAP / IFRS Indirect Method
                    </span>
                </div>
                <h1 class="fr-banner-title">
                    STATEMENT OF CASH FLOWS
                </h1>
                <p class="fr-banner-subtitle">
                    Reconciliation of net operating profit to actual cash flows from {{ $startDate }} to {{ $endDate }}
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Currency</div>
                    <div class="fr-banner-card-val">USD ($)</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Reconciliation Status</div>
                    <div class="fr-banner-card-val" style="color: {{ $isBalanced ? '#34d399' : '#f87171' }}; font-size: 0.95rem;">
                        {{ $isBalanced ? 'Balanced (100%)' : 'Discrepancy' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="fr-card">
            <div class="fr-filters-grid cols-3">
                <div class="fr-filter-group">
                    <label class="fr-filter-label">From Date</label>
                    <input 
                        type="date" 
                        wire:model.live="startDate" 
                        class="fr-filter-input"
                    />
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">To Date</label>
                    <input 
                        type="date" 
                        wire:model.live="endDate" 
                        class="fr-filter-input"
                    />
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">Branch Location</label>
                    <select 
                        wire:model.live="branchId" 
                        class="fr-filter-input"
                    >
                        <option value="">All Branches (Consolidated)</option>
                        @foreach($this->branches as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="fr-kpi-grid cols-4">
            {{-- Operating Cash Flow --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Operating Activities</span>
                    <div class="fr-kpi-icon-box {{ $netOperating >= 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $netOperating >= 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        ${{ number_format($netOperating, 2) }}
                    </div>
                    <div class="fr-kpi-desc">Core operations &amp; working capital</div>
                </div>
            </div>

            {{-- Investing Cash Flow --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Investing Activities</span>
                    <div class="fr-kpi-icon-box {{ $netInvesting >= 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="heroicon-o-building-office" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $netInvesting >= 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        ${{ number_format($netInvesting, 2) }}
                    </div>
                    <div class="fr-kpi-desc">Capital expenditures &amp; fixed assets</div>
                </div>
            </div>

            {{-- Financing Cash Flow --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Financing Activities</span>
                    <div class="fr-kpi-icon-box {{ $netFinancing >= 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $netFinancing >= 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        ${{ number_format($netFinancing, 2) }}
                    </div>
                    <div class="fr-kpi-desc">Equity injections &amp; long-term debt</div>
                </div>
            </div>

            {{-- Net Cash Change --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Net Cash Movement</span>
                    <div class="fr-kpi-icon-box {{ $computedNetCash >= 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="heroicon-o-scale" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $computedNetCash >= 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        ${{ number_format($computedNetCash, 2) }}
                    </div>
                    <div class="fr-kpi-desc">Period net liquidity change</div>
                </div>
            </div>
        </div>

        {{-- Cash Reconciliation Banner --}}
        <div class="fr-card" style="border-left: 4px solid {{ $isBalanced ? '#10b981' : '#f43f5e' }};">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="fr-kpi-icon-box {{ $isBalanced ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="{{ $isBalanced ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-circle' }}" class="h-6 w-6" style="width: 24px; height: 24px;" />
                    </div>
                    <div>
                        <h4 style="font-weight: 800; margin: 0; font-size: 1rem;">
                            {{ $isBalanced ? 'Cash Reconciliation Equilibrium: Verified Balanced' : 'Cash Flow Reconciliation Discrepancy' }}
                        </h4>
                        <p style="margin: 0.2rem 0 0 0; font-size: 0.8125rem; color: #64748b;">
                            Beginning Cash (${{ number_format($beginningCash, 2) }}) + Net Period Movement (${{ number_format($computedNetCash, 2) }}) = Ending Cash (${{ number_format($endingCash, 2) }})
                        </p>
                    </div>
                </div>

                <div style="text-align: right;">
                    <span class="fr-tag" style="background: {{ $isBalanced ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)' }}; color: {{ $isBalanced ? '#059669' : '#e11d48' }}; font-size: 0.875rem; font-weight: 800;">
                        Ending Cash: ${{ number_format($endingCash, 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Cash Flow Statement Sections --}}
        <div class="fr-table-card">
            <div class="fr-table-header-bar">
                <div>
                    <h3 class="fr-table-heading">Statement of Cash Flows (Indirect Method Breakdown)</h3>
                    <p class="fr-table-subheading">Operating, investing, and financing details conforming to IFRS / US GAAP standards</p>
                </div>
            </div>

            <div class="fr-table-responsive">
                <table class="fr-table">
                    <thead>
                        <tr>
                            <th>Activity / Account Line Item</th>
                            <th style="width: 140px; text-align: right;">Beginning ($)</th>
                            <th style="width: 140px; text-align: right;">Ending ($)</th>
                            <th style="width: 140px; text-align: right;">Balance &Delta; ($)</th>
                            <th style="width: 170px; text-align: right;">Cash Impact ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 1. OPERATING ACTIVITIES --}}
                        <tr style="background: rgba(15, 23, 42, 0.05); font-weight: 800;">
                            <td colspan="5" style="color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem;">
                                1. Cash Flows from Operating Activities
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700; padding-left: 1.5rem;">Net Income (Loss) for the Period</td>
                            <td colspan="3"></td>
                            <td class="fr-num" style="font-weight: 800; color: {{ $netIncome >= 0 ? '#059669' : '#e11d48' }};">
                                ${{ number_format($netIncome, 2) }}
                            </td>
                        </tr>
                        @if($depreciation > 0)
                            <tr>
                                <td style="padding-left: 2rem;">Adjustments: Depreciation &amp; Amortization (Non-Cash)</td>
                                <td colspan="3"></td>
                                <td class="fr-num" style="font-weight: 700; color: #059669;">
                                    +${{ number_format($depreciation, 2) }}
                                </td>
                            </tr>
                        @endif

                        <tr style="background: rgba(148, 163, 184, 0.06);">
                            <td colspan="5" style="padding-left: 1.5rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b;">
                                Changes in Operating Assets &amp; Liabilities (Working Capital):
                            </td>
                        </tr>

                        @forelse($operatingAssets as $item)
                            <tr>
                                <td style="padding-left: 2.25rem;">
                                    {{ $item['cash_effect'] < 0 ? 'Increase' : 'Decrease' }} in {{ $item['account_name'] }}
                                </td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['start_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['end_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['delta'], 2) }}</td>
                                <td class="fr-num" style="font-weight: 700; color: {{ $item['cash_effect'] >= 0 ? '#059669' : '#e11d48' }};">
                                    {{ $item['cash_effect'] >= 0 ? '$' . number_format($item['cash_effect'], 2) : '($' . number_format(abs($item['cash_effect']), 2) . ')' }}
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        @forelse($operatingLiabs as $item)
                            <tr>
                                <td style="padding-left: 2.25rem;">
                                    {{ $item['cash_effect'] >= 0 ? 'Increase' : 'Decrease' }} in {{ $item['account_name'] }}
                                </td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['start_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['end_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['delta'], 2) }}</td>
                                <td class="fr-num" style="font-weight: 700; color: {{ $item['cash_effect'] >= 0 ? '#059669' : '#e11d48' }};">
                                    {{ $item['cash_effect'] >= 0 ? '$' . number_format($item['cash_effect'], 2) : '($' . number_format(abs($item['cash_effect']), 2) . ')' }}
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        <tr style="background: rgba(16, 185, 129, 0.08); font-weight: 800;">
                            <td colspan="4" style="padding-left: 1.5rem; text-transform: uppercase; font-size: 0.8125rem;">
                                Net Cash Provided by (Used in) Operating Activities
                            </td>
                            <td class="fr-num" style="font-size: 0.95rem; font-weight: 900; color: {{ $netOperating >= 0 ? '#059669' : '#e11d48' }};">
                                {{ $netOperating >= 0 ? '$' . number_format($netOperating, 2) : '($' . number_format(abs($netOperating), 2) . ')' }}
                            </td>
                        </tr>

                        {{-- 2. INVESTING ACTIVITIES --}}
                        <tr style="background: rgba(15, 23, 42, 0.05); font-weight: 800;">
                            <td colspan="5" style="color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem;">
                                2. Cash Flows from Investing Activities
                            </td>
                        </tr>
                        @forelse($investingItems as $item)
                            <tr>
                                <td style="padding-left: 2rem;">
                                    {{ $item['cash_effect'] < 0 ? 'Purchase / Addition:' : 'Disposal:' }} {{ $item['account_name'] }}
                                </td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['start_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['end_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['delta'], 2) }}</td>
                                <td class="fr-num" style="font-weight: 700; color: {{ $item['cash_effect'] >= 0 ? '#059669' : '#e11d48' }};">
                                    {{ $item['cash_effect'] >= 0 ? '$' . number_format($item['cash_effect'], 2) : '($' . number_format(abs($item['cash_effect']), 2) . ')' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding-left: 2rem; color: #94a3b8; font-style: italic;">
                                    No capital expenditure or non-current asset movements in this period.
                                </td>
                            </tr>
                        @endforelse
                        <tr style="background: rgba(99, 102, 241, 0.08); font-weight: 800;">
                            <td colspan="4" style="padding-left: 1.5rem; text-transform: uppercase; font-size: 0.8125rem;">
                                Net Cash Provided by (Used in) Investing Activities
                            </td>
                            <td class="fr-num" style="font-size: 0.95rem; font-weight: 900; color: {{ $netInvesting >= 0 ? '#059669' : '#e11d48' }};">
                                {{ $netInvesting >= 0 ? '$' . number_format($netInvesting, 2) : '($' . number_format(abs($netInvesting), 2) . ')' }}
                            </td>
                        </tr>

                        {{-- 3. FINANCING ACTIVITIES --}}
                        <tr style="background: rgba(15, 23, 42, 0.05); font-weight: 800;">
                            <td colspan="5" style="color: #ec4899; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem;">
                                3. Cash Flows from Financing Activities
                            </td>
                        </tr>
                        @forelse($financingItems as $item)
                            <tr>
                                <td style="padding-left: 2rem;">{{ $item['account_name'] }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['start_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['end_balance'], 2) }}</td>
                                <td class="fr-num" style="color: #64748b;">${{ number_format($item['delta'], 2) }}</td>
                                <td class="fr-num" style="font-weight: 700; color: {{ $item['cash_effect'] >= 0 ? '#059669' : '#e11d48' }};">
                                    {{ $item['cash_effect'] >= 0 ? '$' . number_format($item['cash_effect'], 2) : '($' . number_format(abs($item['cash_effect']), 2) . ')' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding-left: 2rem; color: #94a3b8; font-style: italic;">
                                    No debt financing or equity capital transactions in this period.
                                </td>
                            </tr>
                        @endforelse
                        <tr style="background: rgba(236, 72, 153, 0.08); font-weight: 800;">
                            <td colspan="4" style="padding-left: 1.5rem; text-transform: uppercase; font-size: 0.8125rem;">
                                Net Cash Provided by (Used in) Financing Activities
                            </td>
                            <td class="fr-num" style="font-size: 0.95rem; font-weight: 900; color: {{ $netFinancing >= 0 ? '#059669' : '#e11d48' }};">
                                {{ $netFinancing >= 0 ? '$' . number_format($netFinancing, 2) : '($' . number_format(abs($netFinancing), 2) . ')' }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid #0f172a;">
                            <td colspan="4" style="font-weight: 800; text-transform: uppercase;">
                                Net Increase / (Decrease) in Cash and Cash Equivalents
                            </td>
                            <td class="fr-num" style="font-size: 1.1rem; font-weight: 900; color: {{ $computedNetCash >= 0 ? '#059669' : '#e11d48' }};">
                                {{ $computedNetCash >= 0 ? '$' . number_format($computedNetCash, 2) : '($' . number_format(abs($computedNetCash), 2) . ')' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-weight: 700; color: #64748b;">
                                Cash and Cash Equivalents at Beginning of Period ({{ $startDate }})
                            </td>
                            <td class="fr-num" style="font-weight: 700; color: #64748b;">
                                ${{ number_format($beginningCash, 2) }}
                            </td>
                        </tr>
                        <tr style="background: rgba(15, 23, 42, 0.08); border-bottom: 3px double #0f172a;">
                            <td colspan="4" style="font-weight: 900; font-size: 1rem;">
                                Cash and Cash Equivalents at End of Period ({{ $endDate }})
                            </td>
                            <td class="fr-num" style="font-size: 1.15rem; font-weight: 900; color: #059669;">
                                ${{ number_format($endingCash, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
