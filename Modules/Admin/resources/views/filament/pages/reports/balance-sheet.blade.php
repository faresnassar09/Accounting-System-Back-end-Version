<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

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

    <div class="fr-wrapper">
        {{-- Professional Executive Report Header --}}
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
                    BALANCE SHEET
                </h1>
                <p class="fr-banner-subtitle">
                    Statement of Financial Position as of <strong style="color: #ffffff;">{{ $asOfFormatted }}</strong>
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Currency</div>
                    <div class="fr-banner-card-val">USD ($)</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Equation Status</div>
                    @if($isBalanced)
                        <div class="fr-banner-card-val" style="color: #34d399;">
                            Balanced (A = L + E)
                        </div>
                    @else
                        <div class="fr-banner-card-val" style="color: #f87171;">
                            Equation Out of Balance
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="fr-card">
            <div class="fr-filters-grid cols-3">
                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        As of Date
                    </label>
                    <input 
                        type="date" 
                        wire:model.live="endDate"
                        class="fr-filter-input"
                    />
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        Branch Location
                    </label>
                    <select 
                        wire:model.live="branchId"
                        class="fr-filter-select"
                    >
                        <option value="">All Branches (Consolidated)</option>
                        @foreach($this->branches as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button 
                        type="button"
                        wire:click="$refresh"
                        class="fr-btn"
                        style="width: 100%;"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" style="width: 16px; height: 16px;" />
                        <span>Update Statement</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Executive KPI Cards --}}
        <div class="fr-kpi-grid cols-4">
            {{-- Total Assets --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Assets</span>
                    <div class="fr-kpi-icon-box fr-bg-blue">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val" style="color: #2563eb;">
                        ${{ number_format($totalAssets, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Total economic resources
                    </div>
                </div>
            </div>

            {{-- Total Liabilities & Equity --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Liabilities &amp; Equity</span>
                    <div class="fr-kpi-icon-box fr-bg-purple">
                        <x-filament::icon icon="heroicon-o-scale" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val" style="color: #7c3aed;">
                        ${{ number_format($totalLiabEquity, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Claims &amp; owner capital
                    </div>
                </div>
            </div>

            {{-- Difference --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Difference</span>
                    <div class="fr-kpi-icon-box {{ $difference == 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="{{ $difference == 0 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle' }}" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $difference == 0 ? '' : 'fr-text-rose' }}">
                        ${{ number_format($difference, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc {{ $difference == 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        {{ $difference == 0 ? 'Exact balance equilibrium' : 'Discrepancy detected' }}
                    </div>
                </div>
            </div>

            {{-- Accounting Equation --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Accounting Equation</span>
                    <div class="fr-kpi-icon-box fr-bg-indigo">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div style="margin-top: 0.65rem;">
                        @if($isBalanced)
                            <span class="fr-badge-success">
                                <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4" style="width: 14px; height: 14px;" />
                                Balanced (A = L + E)
                            </span>
                        @else
                            <span class="fr-badge-danger">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-4 h-4" style="width: 14px; height: 14px;" />
                                Equation Out of Balance
                            </span>
                        @endif
                    </div>
                    <div class="fr-kpi-desc">
                        Fundamental IFRS validation
                    </div>
                </div>
            </div>
        </div>

        {{-- Classified Balance Sheet Sections (2-Column Grid) --}}
        <div class="fr-two-col">

            {{-- LEFT COLUMN: ASSETS --}}
            <div class="fr-table-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div class="fr-table-header-bar" style="border-bottom: 2px solid #2563eb;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="fr-kpi-icon-box fr-bg-blue" style="width: 30px; height: 30px;">
                                <x-filament::icon icon="heroicon-o-building-office-2" class="h-4 w-4" style="width: 16px; height: 16px;" />
                            </div>
                            <h2 style="font-size: 1rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                                Assets
                            </h2>
                        </div>
                        <span class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #2563eb;">
                            ${{ number_format($totalAssets, 2) }}
                        </span>
                    </div>

                    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.5rem;">
                        @forelse($assetSubTypes as $subKey => $subType)
                            @php
                                $subTypeName = is_array($subType) ? ($subType['type_name'] ?? ucwords(str_replace('_', ' ', $subKey))) : ($subType->type_name ?? ucwords(str_replace('_', ' ', $subKey)));
                                $subTypeTotal = (float) (is_array($subType) ? ($subType['type_total'] ?? 0) : ($subType->type_total ?? 0));
                                $accounts = is_array($subType) ? ($subType['accounts'] ?? []) : ($subType->accounts ?? []);
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(148, 163, 184, 0.08); padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <span>{{ $subTypeName }}</span>
                                    <span class="fr-num">${{ number_format($subTypeTotal, 2) }}</span>
                                </div>

                                <table class="fr-table" style="margin-top: 0.5rem;">
                                    <tbody>
                                        @forelse($accounts as $acc)
                                            @php
                                                $accObj = is_array($acc) ? (object) $acc : $acc;
                                                $accName = $accObj->name ?? $accObj->account_name ?? 'Account';
                                                $accNumber = $accObj->number ?? $accObj->account_number ?? '';
                                                $accBal = (float) ($accObj->netBalance ?? $accObj->netbalance ?? $accObj->balance ?? 0);
                                            @endphp
                                            <tr>
                                                <td style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #f59e0b; width: 100px;">
                                                    {{ !empty($accNumber) ? '#' . $accNumber : '—' }}
                                                </td>
                                                <td style="font-weight: 500;">
                                                    {{ $accName }}
                                                </td>
                                                <td class="fr-num" style="width: 140px;">
                                                    ${{ number_format($accBal, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" style="padding: 0.75rem; font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                                                    No account balances in this category.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-style: italic; font-size: 0.875rem;">
                                No asset accounts recorded.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Left Bottom Total --}}
                <div style="border-top: 2px solid #e2e8f0; background: rgba(37, 99, 235, 0.04); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.8125rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">
                        Total Assets
                    </span>
                    <div style="border-bottom: 4px double #2563eb; padding-bottom: 0.25rem;">
                        <span class="fr-num" style="font-size: 1.35rem; font-weight: 900; color: #2563eb;">
                            ${{ number_format($totalAssets, 2) }}
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #2563eb;">USD</span>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: LIABILITIES & EQUITY --}}
            <div class="fr-table-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div class="fr-table-header-bar" style="border-bottom: 2px solid #7c3aed;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="fr-kpi-icon-box fr-bg-purple" style="width: 30px; height: 30px;">
                                <x-filament::icon icon="heroicon-o-scale" class="h-4 w-4" style="width: 16px; height: 16px;" />
                            </div>
                            <h2 style="font-size: 1rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                                Liabilities &amp; Owner Equity
                            </h2>
                        </div>
                        <span class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #7c3aed;">
                            ${{ number_format($totalLiabEquity, 2) }}
                        </span>
                    </div>

                    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.5rem;">
                        @forelse($liabEquitySubTypes as $subKey => $subType)
                            @php
                                $subTypeName = is_array($subType) ? ($subType['type_name'] ?? ucwords(str_replace('_', ' ', $subKey))) : ($subType->type_name ?? ucwords(str_replace('_', ' ', $subKey)));
                                $subTypeTotal = (float) (is_array($subType) ? ($subType['type_total'] ?? 0) : ($subType->type_total ?? 0));
                                $accounts = is_array($subType) ? ($subType['accounts'] ?? []) : ($subType->accounts ?? []);
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(148, 163, 184, 0.08); padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <span>{{ $subTypeName }}</span>
                                    <span class="fr-num">${{ number_format($subTypeTotal, 2) }}</span>
                                </div>

                                <table class="fr-table" style="margin-top: 0.5rem;">
                                    <tbody>
                                        @forelse($accounts as $acc)
                                            @php
                                                $accObj = is_array($acc) ? (object) $acc : $acc;
                                                $accName = $accObj->name ?? $accObj->account_name ?? 'Account';
                                                $accNumber = $accObj->number ?? $accObj->account_number ?? '';
                                                $accBal = (float) ($accObj->netBalance ?? $accObj->netbalance ?? $accObj->balance ?? 0);
                                            @endphp
                                            <tr>
                                                <td style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #f59e0b; width: 100px;">
                                                    {{ !empty($accNumber) ? '#' . $accNumber : '—' }}
                                                </td>
                                                <td style="font-weight: 500;">
                                                    {{ $accName }}
                                                </td>
                                                <td class="fr-num" style="width: 140px;">
                                                    ${{ number_format($accBal, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" style="padding: 0.75rem; font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                                                    No account balances in this category.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-style: italic; font-size: 0.875rem;">
                                No liabilities or equity recorded.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Right Bottom Total --}}
                <div style="border-top: 2px solid #e2e8f0; background: rgba(124, 58, 237, 0.04); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.8125rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">
                        Total Liabilities &amp; Equity
                    </span>
                    <div style="border-bottom: 4px double #7c3aed; padding-bottom: 0.25rem;">
                        <span class="fr-num" style="font-size: 1.35rem; font-weight: 900; color: #7c3aed;">
                            ${{ number_format($totalLiabEquity, 2) }}
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #7c3aed;">USD</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Accounting Equation Equilibrium Banner --}}
        <div style="border-radius: 1rem; padding: 1.25rem 1.5rem; border: 2px solid {{ $isBalanced ? 'rgba(16, 185, 129, 0.4)' : 'rgba(244, 63, 94, 0.4)' }}; background: {{ $isBalanced ? 'rgba(16, 185, 129, 0.06)' : 'rgba(244, 63, 94, 0.06)' }}; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="fr-kpi-icon-box {{ $isBalanced ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                    <x-filament::icon icon="{{ $isBalanced ? 'heroicon-o-check-badge' : 'heroicon-o-shield-exclamation' }}" class="h-6 w-6" style="width: 24px; height: 24px;" />
                </div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $isBalanced ? '#059669' : '#e11d48' }};">
                        {{ $isBalanced ? 'Double-Entry Equilibrium Verified' : 'Double-Entry Discrepancy Detected' }}
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0;">
                        {{ $isBalanced ? 'Total Assets strictly equal Total Liabilities plus Owner Equity.' : 'Assets do not equal Liabilities plus Equity. Check unposted journal adjustments.' }}
                    </p>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875rem; font-weight: 900;">
                <span style="color: #2563eb;">Assets ${{ number_format($totalAssets, 2) }}</span>
                <span style="color: #94a3b8;">=</span>
                <span style="color: #7c3aed;">Liab &amp; Eq ${{ number_format($totalLiabEquity, 2) }}</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>

