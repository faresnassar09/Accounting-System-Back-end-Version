<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

    @php
        $data = $this->reportData;
        $totalBudgeted = (float) ($data['total_budgeted'] ?? 0);
        $totalActual = (float) ($data['total_actual'] ?? 0);
        $overallVariance = (float) ($data['overall_variance'] ?? 0);
        $overallUtilization = (float) ($data['overall_utilization'] ?? 0);
        $items = $data['items'] ?? [];
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
                </div>
                <h1 class="fr-banner-title">
                    BUDGET VS. ACTUAL VARIANCE REPORT
                </h1>
                <p class="fr-banner-subtitle">
                    Comprehensive comparison of annual allocated budgets against actual general ledger performance for Fiscal Year <strong style="color: #ffffff;">{{ $fiscalYear }}</strong>
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Currency</div>
                    <div class="fr-banner-card-val">USD ($)</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Overall Utilization</div>
                    <div class="fr-banner-card-val" style="color: {{ $overallUtilization > 100 ? '#f87171' : ($overallUtilization >= 85 ? '#f59e0b' : '#34d399') }};">
                        {{ number_format($overallUtilization, 1) }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="fr-card">
            <div class="fr-filters-grid cols-3">
                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        Fiscal Year
                    </label>
                    <select 
                        wire:model.live="fiscalYear"
                        class="fr-filter-input"
                    >
                        @foreach($this->availableYears as $yearVal => $yearLabel)
                            <option value="{{ $yearVal }}">{{ $yearLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        Branch Location
                    </label>
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

                <div class="fr-filter-group" style="justify-content: flex-end;">
                    <button 
                        type="button" 
                        wire:click="$refresh"
                        class="fr-btn fr-btn-primary"
                        style="width: 100%;"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" style="width: 16px; height: 16px;" />
                        <span>Update Variance</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Executive KPI Metrics Cards --}}
        <div class="fr-kpi-grid cols-4">
            {{-- Total Budgeted --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Allocated Budget</span>
                    <div class="fr-kpi-icon-box fr-bg-indigo">
                        <x-filament::icon icon="heroicon-o-calculator" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val" style="color: #6366f1;">
                        ${{ number_format($totalBudgeted, 2) }}
                    </div>
                    <div class="fr-kpi-desc">Total planned expenditure/targets</div>
                </div>
            </div>

            {{-- Total Actual --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Actual (Ledger)</span>
                    <div class="fr-kpi-icon-box fr-bg-emerald">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val fr-text-emerald">
                        ${{ number_format($totalActual, 2) }}
                    </div>
                    <div class="fr-kpi-desc">Actual posted transactions</div>
                </div>
            </div>

            {{-- Variance --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Remaining Variance</span>
                    <div class="fr-kpi-icon-box {{ $overallVariance >= 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="{{ $overallVariance >= 0 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle' }}" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $overallVariance >= 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        ${{ number_format($overallVariance, 2) }}
                    </div>
                    <div class="fr-kpi-desc">
                        {{ $overallVariance >= 0 ? 'Under total allocated budget' : 'Over total allocated budget' }}
                    </div>
                </div>
            </div>

            {{-- Overall Utilization --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Budget Burn Rate</span>
                    <div class="fr-kpi-icon-box {{ $overallUtilization > 100 ? 'fr-bg-rose' : 'fr-bg-indigo' }}">
                        <x-filament::icon icon="heroicon-o-chart-pie" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $overallUtilization > 100 ? 'fr-text-rose' : '' }}">
                        {{ number_format($overallUtilization, 1) }}%
                    </div>
                    <div class="fr-kpi-desc">
                        {{ count($items) }} active budget targets
                    </div>
                </div>
            </div>
        </div>

        {{-- Variance Table Card --}}
        <div class="fr-table-card">
            <div class="fr-table-header-bar">
                <div>
                    <h3 class="fr-table-heading">Budget Accounts Breakdown &amp; Performance</h3>
                    <p class="fr-table-subheading">Account-by-account variance comparison with visual utilization indicators</p>
                </div>
                <span class="fr-tag" style="background: rgba(148, 163, 184, 0.15); color: inherit;">
                    {{ count($items) }} Accounts
                </span>
            </div>

            <div class="fr-table-responsive">
                <table class="fr-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Account Code</th>
                            <th>Account Title</th>
                            <th style="width: 140px;">Dimension</th>
                            <th style="text-align: right; width: 170px;">Budget Target ($)</th>
                            <th style="text-align: right; width: 170px;">Actual ($)</th>
                            <th style="text-align: right; width: 170px;">Variance ($)</th>
                            <th style="width: 170px;">Utilization (%)</th>
                            <th style="width: 140px; text-align: center;">Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            @php
                                $utilPct = min(100, max(0, $row['utilization']));
                                $statusBadgeBg = match($row['status_color']) {
                                    'emerald' => 'rgba(16, 185, 129, 0.15)',
                                    'amber'   => 'rgba(245, 158, 11, 0.15)',
                                    'rose'    => 'rgba(244, 63, 94, 0.15)',
                                    default   => 'rgba(148, 163, 184, 0.15)',
                                };
                                $statusTextColor = match($row['status_color']) {
                                    'emerald' => '#059669',
                                    'amber'   => '#d97706',
                                    'rose'    => '#e11d48',
                                    default   => '#64748b',
                                };
                            @endphp
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: #f59e0b;">
                                    #{{ $row['account_number'] }}
                                </td>
                                <td style="font-weight: 600;">
                                    {{ $row['account_name'] }}
                                </td>
                                <td>
                                    <span class="fr-tag" style="background: rgba(148, 163, 184, 0.12); color: inherit;">
                                        {{ $row['branch_name'] }}
                                    </span>
                                </td>
                                <td class="fr-num" style="font-weight: 700; color: #6366f1;">
                                    ${{ number_format($row['budgeted'], 2) }}
                                </td>
                                <td class="fr-num" style="font-weight: 700;">
                                    ${{ number_format($row['actual'], 2) }}
                                </td>
                                <td class="fr-num" style="font-weight: 700; color: {{ $row['variance'] >= 0 ? '#059669' : '#e11d48' }};">
                                    {{ $row['variance'] >= 0 ? '$' . number_format($row['variance'], 2) : '($' . number_format(abs($row['variance']), 2) . ')' }}
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="flex: 1; height: 8px; background: rgba(148, 163, 184, 0.2); border-radius: 9999px; overflow: hidden;">
                                            <div style="height: 100%; width: {{ $utilPct }}%; background: {{ $statusTextColor }}; border-radius: 9999px;"></div>
                                        </div>
                                        <span style="font-size: 0.75rem; font-weight: 700; min-width: 42px; text-align: right;">
                                            {{ number_format($row['utilization'], 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="fr-tag" style="background: {{ $statusBadgeBg }}; color: {{ $statusTextColor }};">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                                    No budget allocations configured for Fiscal Year {{ $fiscalYear }}. Create budget targets via the Budgets &amp; Planning menu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                Total Statements Summary
                            </td>
                            <td class="fr-num" style="font-size: 1rem; font-weight: 900; color: #6366f1;">
                                ${{ number_format($totalBudgeted, 2) }}
                            </td>
                            <td class="fr-num" style="font-size: 1rem; font-weight: 900; color: #059669;">
                                ${{ number_format($totalActual, 2) }}
                            </td>
                            <td class="fr-num" style="font-size: 1rem; font-weight: 900; color: {{ $overallVariance >= 0 ? '#059669' : '#e11d48' }};">
                                {{ $overallVariance >= 0 ? '$' . number_format($overallVariance, 2) : '($' . number_format(abs($overallVariance), 2) . ')' }}
                            </td>
                            <td colspan="2" style="text-align: right; font-weight: 800; font-size: 0.875rem;">
                                Rate: {{ number_format($overallUtilization, 1) }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
