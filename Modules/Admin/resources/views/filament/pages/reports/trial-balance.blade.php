<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

    @php
        $reportData = $this->getReportData();
        $accounts = $reportData['reportData'] ?? $reportData['accounts'] ?? [];
        $totals = $reportData['totals'] ?? [];
        $totalDebit = (float) ($totals['total_debit'] ?? 0);
        $totalCredit = (float) ($totals['total_credit'] ?? 0);
        $isBalanced = (bool) ($totals['isBalanced'] ?? ($totalDebit === $totalCredit));
        $difference = abs($totalDebit - $totalCredit);
        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
        $asOfDateFormatted = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('F d, Y') : now()->format('F d, Y');
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
                    TRIAL BALANCE
                </h1>
                <p class="fr-banner-subtitle">
                    Financial ledger balances as of <strong style="color: #ffffff;">{{ $asOfDateFormatted }}</strong>
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Currency</div>
                    <div class="fr-banner-card-val">USD ($)</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Ledger Status</div>
                    @if($isBalanced)
                        <div class="fr-banner-card-val" style="color: #34d399;">
                            Balanced
                        </div>
                    @else
                        <div class="fr-banner-card-val" style="color: #f87171;">
                            Unbalanced
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
            {{-- Total Debits --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Debits</span>
                    <div class="fr-kpi-icon-box fr-bg-indigo">
                        <x-filament::icon icon="heroicon-o-arrow-down-left" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val">
                        ${{ number_format($totalDebit, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Cumulative debit movements
                    </div>
                </div>
            </div>

            {{-- Total Credits --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Credits</span>
                    <div class="fr-kpi-icon-box fr-bg-indigo">
                        <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val">
                        ${{ number_format($totalCredit, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Cumulative credit movements
                    </div>
                </div>
            </div>

            {{-- Balance Difference --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Difference</span>
                    <div class="fr-kpi-icon-box {{ $difference == 0 ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="heroicon-o-scale" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $difference == 0 ? '' : 'fr-text-rose' }}">
                        ${{ number_format($difference, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc {{ $difference == 0 ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        {{ $difference == 0 ? 'Exact debit & credit equilibrium' : 'Investigate posting discrepancies' }}
                    </div>
                </div>
            </div>

            {{-- Ledger Status --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Verification</span>
                    <div class="fr-kpi-icon-box {{ $isBalanced ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="{{ $isBalanced ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-triangle' }}" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div style="margin-top: 0.65rem;">
                        @if($isBalanced)
                            <span class="fr-badge-success">
                                <span style="width: 6px; height: 6px; border-radius: 9999px; background: currentColor;"></span>
                                Balanced
                            </span>
                        @else
                            <span class="fr-badge-danger">
                                <span style="width: 6px; height: 6px; border-radius: 9999px; background: currentColor;"></span>
                                Out of Balance
                            </span>
                        @endif
                    </div>
                    <div class="fr-kpi-desc">
                        GAAP/IFRS double-entry integrity
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Data Table --}}
        <div class="fr-table-card">
            <div class="fr-table-header-bar">
                <div>
                    <h3 class="fr-table-heading">Chart of Accounts Breakdown</h3>
                    <p class="fr-table-subheading">Summary of period activity and ending balances by account</p>
                </div>
                <span class="fr-tag" style="background: rgba(148, 163, 184, 0.15); color: inherit; border-color: rgba(148, 163, 184, 0.3);">
                    {{ count($accounts) }} Accounts
                </span>
            </div>

            <div class="fr-table-responsive">
                <table class="fr-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Account Code</th>
                            <th>Account Title</th>
                            <th style="text-align: right; width: 180px;">Period Debit</th>
                            <th style="text-align: right; width: 180px;">Period Credit</th>
                            <th style="text-align: right; width: 200px;">Debit Balance (USD)</th>
                            <th style="text-align: right; width: 200px;">Credit Balance (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                            @php
                                $accObj = is_array($acc) ? (object) $acc : $acc;
                                $code = $accObj->number ?? $accObj->account_number ?? '';
                                $name = $accObj->name ?? $accObj->account_name ?? 'Account';
                                $periodDebit = (float) ($accObj->period_debit ?? 0);
                                $periodCredit = (float) ($accObj->period_credit ?? 0);
                                $finalDebit = (float) ($accObj->final_debit_balance ?? $accObj->debit ?? 0);
                                $finalCredit = (float) ($accObj->final_credit_balance ?? $accObj->credit ?? 0);
                            @endphp
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: #f59e0b;">
                                    {{ $code ? '#' . $code : '—' }}
                                </td>
                                <td style="font-weight: 600;">
                                    {{ $name }}
                                </td>
                                <td class="fr-num" style="color: #64748b;">
                                    {{ $periodDebit > 0 ? number_format($periodDebit, 2) : '—' }}
                                </td>
                                <td class="fr-num" style="color: #64748b;">
                                    {{ $periodCredit > 0 ? number_format($periodCredit, 2) : '—' }}
                                </td>
                                <td class="fr-num" style="font-weight: 700;">
                                    {{ $finalDebit > 0 ? number_format($finalDebit, 2) : '—' }}
                                </td>
                                <td class="fr-num" style="font-weight: 700;">
                                    {{ $finalCredit > 0 ? number_format($finalCredit, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                                    No accounts or transactions recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                Total Statement Balances
                            </td>
                            <td class="fr-num" style="font-size: 1rem; font-weight: 900; color: #059669;">
                                ${{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="fr-num" style="font-size: 1rem; font-weight: 900; color: #059669;">
                                ${{ number_format($totalCredit, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
