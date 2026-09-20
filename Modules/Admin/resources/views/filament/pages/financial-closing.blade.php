<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

    @php
        $pnl = $this->pnlPreview;
        $isClosed = $this->isClosed;
        $totalRevenues = (float) ($pnl['total_revenues'] ?? 0);
        $totalExpenses = (float) ($pnl['total_expenses'] ?? 0);
        $netProfit = (float) ($pnl['net_profit'] ?? 0);
        $isProfit = ($netProfit >= 0);
        $closedYears = $this->closedYears;
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
                    @if($isClosed)
                        <span class="fr-tag" style="background: rgba(244, 63, 94, 0.2); color: #f87171; border-color: rgba(244, 63, 94, 0.4);">
                            &bull; Fiscal Year {{ $selectedYear }} Closed &amp; Locked
                        </span>
                    @else
                        <span class="fr-tag" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: rgba(16, 185, 129, 0.4);">
                            &bull; Fiscal Year {{ $selectedYear }} Open for Postings
                        </span>
                    @endif
                </div>
                <h1 class="fr-banner-title">
                    FINANCIAL YEAR-END CLOSING &amp; ROLL-FORWARD
                </h1>
                <p class="fr-banner-subtitle">
                    Close nominal accounts (Revenues &amp; Expenses), roll forward net income into Retained Earnings, and generate next year's opening balances.
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Selected Fiscal Year</div>
                    <div class="fr-banner-card-val">{{ $selectedYear }}</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Period Status</div>
                    <div class="fr-banner-card-val" style="color: {{ $isClosed ? '#f87171' : '#34d399' }};">
                        {{ $isClosed ? 'LOCKED / CLOSED' : 'ACTIVE / OPEN' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Closing Controller Panel --}}
        <div class="fr-card">
            <h3 style="font-size: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 1.25rem;">
                Year-End Period Control
            </h3>

            <div class="fr-filters-grid cols-3">
                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        Select Fiscal Year
                    </label>
                    <select 
                        wire:model.live="selectedYear"
                        class="fr-filter-input"
                    >
                        @foreach($this->availableYears as $yearVal => $yearLabel)
                            <option value="{{ $yearVal }}">{{ $yearLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        Destination Retained Earnings Account
                    </label>
                    <select 
                        wire:model="retainedEarningsAccountId"
                        class="fr-filter-input"
                        @if($isClosed) disabled @endif
                    >
                        <option value="">-- Choose Equity Account --</option>
                        @foreach($this->equityAccounts as $accId => $accTitle)
                            <option value="{{ $accId }}">{{ $accTitle }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fr-filter-group" style="justify-content: flex-end;">
                    @if($isClosed)
                        <button 
                            type="button" 
                            disabled
                            class="fr-btn"
                            style="width: 100%; opacity: 0.6; cursor: not-allowed; background: rgba(148, 163, 184, 0.2); color: inherit;"
                        >
                            <x-filament::icon icon="heroicon-o-lock-closed" class="h-4 w-4" style="width: 16px; height: 16px;" />
                            <span>Year {{ $selectedYear }} Is Closed</span>
                        </button>
                    @else
                        <button 
                            type="button" 
                            wire:click="applyClosing"
                            wire:confirm="Are you sure you want to permanently close financial year {{ $selectedYear }}? This will zero out all revenue and expense accounts, transfer net income to the chosen equity account, post opening balances for {{ $selectedYear + 1 }}, and lock year {{ $selectedYear }} from further journal entries."
                            class="fr-btn"
                            style="width: 100%; background: #e11d48; color: #ffffff; border-color: #be123c;"
                        >
                            <x-filament::icon icon="heroicon-o-lock-closed" class="h-4 w-4" style="width: 16px; height: 16px;" />
                            <span>Execute Year Closing</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Live P&L Preview KPIs before closing --}}
        <div>
            <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 0.75rem;">
                Fiscal Year {{ $selectedYear }} Performance Preview (Pre-Closing)
            </div>

            <div class="fr-kpi-grid cols-4">
                {{-- Total Revenues --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Total Revenues</span>
                        <div class="fr-kpi-icon-box fr-bg-emerald">
                            <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val fr-text-emerald">
                            ${{ number_format($totalRevenues, 2) }}
                        </div>
                        <div class="fr-kpi-desc">To be debited and zeroed</div>
                    </div>
                </div>

                {{-- Total Expenses --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Total Expenses</span>
                        <div class="fr-kpi-icon-box fr-bg-rose">
                            <x-filament::icon icon="heroicon-o-credit-card" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val" style="color: #e11d48;">
                            ${{ number_format($totalExpenses, 2) }}
                        </div>
                        <div class="fr-kpi-desc">To be credited and zeroed</div>
                    </div>
                </div>

                {{-- Net Income to Retained Earnings --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Net Result to Transfer</span>
                        <div class="fr-kpi-icon-box {{ $isProfit ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                            <x-filament::icon icon="{{ $isProfit ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-circle' }}" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val {{ $isProfit ? 'fr-text-emerald' : 'fr-text-rose' }}">
                            ${{ number_format($netProfit, 2) }}
                        </div>
                        <div class="fr-kpi-desc" style="font-weight: 700; color: {{ $isProfit ? '#059669' : '#e11d48' }};">
                            {{ $isProfit ? 'Net Profit (Credit Equity)' : 'Net Loss (Debit Equity)' }}
                        </div>
                    </div>
                </div>

                {{-- Period Lock Status --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Period Audit Status</span>
                        <div class="fr-kpi-icon-box {{ $isClosed ? 'fr-bg-rose' : 'fr-bg-indigo' }}">
                            <x-filament::icon icon="{{ $isClosed ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open' }}" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val" style="color: {{ $isClosed ? '#e11d48' : '#4f46e5' }};">
                            {{ $isClosed ? 'Closed' : 'Open' }}
                        </div>
                        <div class="fr-kpi-desc">
                            {{ $isClosed ? 'Transactions locked' : 'Postings allowed' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- History of Closed Financial Years --}}
        <div class="fr-table-card">
            <div class="fr-table-header-bar">
                <div>
                    <h3 class="fr-table-heading">Closed Financial Years History</h3>
                    <p class="fr-table-subheading">Audit trail of previously finalized and locked fiscal periods</p>
                </div>
                <span class="fr-tag" style="background: rgba(148, 163, 184, 0.15); color: inherit;">
                    {{ count($closedYears) }} Closed Periods
                </span>
            </div>

            <div class="fr-table-responsive">
                <table class="fr-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Fiscal Year</th>
                            <th style="width: 220px;">Closing Execution Date</th>
                            <th style="text-align: right; width: 220px;">Net Income / (Loss) Transferred</th>
                            <th>Retained Earnings Account</th>
                            <th style="width: 150px; text-align: center;">Lock Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($closedYears as $cy)
                            <tr>
                                <td style="font-weight: 800; font-size: 1rem; color: #f59e0b;">
                                    FY {{ $cy->year }}
                                </td>
                                <td style="color: #64748b;">
                                    {{ $cy->created_at ? $cy->created_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="fr-num" style="font-weight: 700; color: {{ (float)$cy->net_profit_loss >= 0 ? '#059669' : '#e11d48' }};">
                                    ${{ number_format((float) $cy->net_profit_loss, 2) }}
                                </td>
                                <td>
                                    Account #{{ $cy->retained_earnings_account_id }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="fr-tag" style="background: rgba(244, 63, 94, 0.15); color: #e11d48; border-color: rgba(244, 63, 94, 0.3);">
                                        Locked
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                                    No closed financial years recorded yet. All fiscal years are currently open.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
