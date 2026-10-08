<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

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
        $startFormatted = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->format('M d, Y') : 'Start';
        $endFormatted = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('M d, Y') : now()->format('M d, Y');
    @endphp

    <div class="fr-wrapper">
        {{-- Executive Report Header Banner --}}
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
                    GENERAL LEDGER
                </h1>
                <p class="fr-banner-subtitle">
                    @if(!empty($accountInfo->name))
                        Account: <strong style="color: #ffffff;">{{ $accountInfo->name }} ({{ !empty($accountInfo->number) ? '#' . $accountInfo->number : 'N/A' }})</strong> &bull;
                    @endif
                    Period: <strong style="color: #ffffff;">{{ $startFormatted }} &rarr; {{ $endFormatted }}</strong>
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Currency</div>
                    <div class="fr-banner-card-val">USD ($)</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Closing Balance</div>
                    <div class="fr-banner-card-val" style="color: #34d399;">
                        ${{ number_format($closingBalance, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="fr-card">
            <div class="fr-filters-grid cols-4">
                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        Target Account
                    </label>
                    <select 
                        wire:model.live="accountId"
                        class="fr-filter-select"
                    >
                        <option value="">Select an Account...</option>
                        @foreach($this->accounts as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        From Date
                    </label>
                    <input 
                        type="date" 
                        wire:model.live="startDate"
                        class="fr-filter-input"
                    />
                </div>

                <div class="fr-filter-group">
                    <label class="fr-filter-label">
                        To Date
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
            </div>
        </div>

        @if($reportData)
            {{-- KPI Summary Cards --}}
            <div class="fr-kpi-grid cols-5">
                {{-- Account Profile --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Account Card</span>
                        <div class="fr-kpi-icon-box fr-bg-amber">
                            <x-filament::icon icon="heroicon-o-book-open" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 1.125rem; font-weight: 800; margin-top: 0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $accountInfo->name ?? '—' }}
                        </div>
                        <div class="fr-kpi-desc">
                            {{ !empty($accountInfo->number) ? '#' . $accountInfo->number : 'General Account' }}
                        </div>
                    </div>
                </div>

                {{-- Opening Balance --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Opening Balance</span>
                        <div class="fr-kpi-icon-box fr-bg-indigo">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val">
                            ${{ number_format($openingBalance, 2) }}
                        </div>
                        <div class="fr-kpi-desc">
                            As of {{ $startFormatted }}
                        </div>
                    </div>
                </div>

                {{-- Total Debits --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Period Debits</span>
                        <div class="fr-kpi-icon-box fr-bg-indigo">
                            <x-filament::icon icon="heroicon-o-arrow-down-left" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val" style="color: #4f46e5;">
                            ${{ number_format($totalDebit, 2) }}
                        </div>
                        <div class="fr-kpi-desc">
                            Total additions
                        </div>
                    </div>
                </div>

                {{-- Total Credits --}}
                <div class="fr-kpi-card">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Period Credits</span>
                        <div class="fr-kpi-icon-box fr-bg-purple">
                            <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val" style="color: #7c3aed;">
                            ${{ number_format($totalCredit, 2) }}
                        </div>
                        <div class="fr-kpi-desc">
                            Total deductions
                        </div>
                    </div>
                </div>

                {{-- Closing Balance --}}
                <div class="fr-kpi-card" style="border-left: 4px solid #f59e0b;">
                    <div class="fr-kpi-header">
                        <span class="fr-kpi-title">Closing Balance</span>
                        <div class="fr-kpi-icon-box fr-bg-emerald">
                            <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" style="width: 20px; height: 20px;" />
                        </div>
                    </div>
                    <div>
                        <div class="fr-kpi-val" style="color: #059669;">
                            ${{ number_format($closingBalance, 2) }}
                        </div>
                        <div class="fr-kpi-desc" style="color: #059669; font-weight: 700;">
                            Position as of {{ $endFormatted }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="fr-table-card">
                <div class="fr-table-header-bar">
                    <div>
                        <h3 class="fr-table-heading">Transaction History & Running Ledger</h3>
                        <p class="fr-table-subheading">Chronological breakdown of postings with cumulative running balance</p>
                    </div>
                    <span class="fr-tag" style="background: rgba(148, 163, 184, 0.15); color: inherit; border-color: rgba(148, 163, 184, 0.3);">
                        {{ count($transactions) }} Postings
                    </span>
                </div>

                <div class="fr-table-responsive">
                    <table class="fr-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Posting Date</th>
                                <th style="width: 140px;">Reference</th>
                                <th>Description</th>
                                <th style="width: 140px;">Branch</th>
                                <th style="text-align: right; width: 160px;">Debit (USD)</th>
                                <th style="text-align: right; width: 160px;">Credit (USD)</th>
                                <th style="text-align: right; width: 180px;">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Opening balance row --}}
                            <tr style="background-color: rgba(245, 158, 11, 0.08); font-weight: 600;">
                                <td style="font-family: monospace; font-size: 0.75rem; color: #64748b;">
                                    {{ $this->startDate }}
                                </td>
                                <td colspan="3" style="color: #b45309; font-weight: 700;">
                                    &bull; OPENING CARRIED-FORWARD BALANCE
                                </td>
                                <td class="fr-num" style="color: #94a3b8;">—</td>
                                <td class="fr-num" style="color: #94a3b8;">—</td>
                                <td class="fr-num" style="font-weight: 900; color: #0f172a;">
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
                                <tr>
                                    <td style="font-family: monospace; font-size: 0.75rem; color: #64748b;">
                                        {{ $txnDate }}
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700; color: #f59e0b;">
                                        {{ $txnRef }}
                                    </td>
                                    <td style="font-weight: 500;">
                                        {{ $txnDesc }}
                                    </td>
                                    <td>
                                        <span class="fr-tag" style="background: rgba(148, 163, 184, 0.1); color: inherit; font-size: 0.6875rem; border-color: rgba(148, 163, 184, 0.2);">
                                            {{ $txnBranch }}
                                        </span>
                                    </td>
                                    <td class="fr-num">
                                        {{ $txnDebit > 0 ? number_format($txnDebit, 2) : '—' }}
                                    </td>
                                    <td class="fr-num">
                                        {{ $txnCredit > 0 ? number_format($txnCredit, 2) : '—' }}
                                    </td>
                                    <td class="fr-num" style="font-weight: 800;">
                                        ${{ number_format($txnRunBal, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                                        No transactions recorded for this account in the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                    Period Activity Totals / Closing Balance
                                </td>
                                <td class="fr-num" style="font-size: 1rem; font-weight: 900;">
                                    ${{ number_format($totalDebit, 2) }}
                                </td>
                                <td class="fr-num" style="font-size: 1rem; font-weight: 900;">
                                    ${{ number_format($totalCredit, 2) }}
                                </td>
                                <td class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #059669;">
                                    ${{ number_format($closingBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="fr-card" style="text-align: center; padding: 4rem 2rem;">
                <x-filament::icon icon="heroicon-o-book-open" class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" style="width: 48px; height: 48px; margin: 0 auto; color: #94a3b8;" />
                <h3 style="font-size: 1.125rem; font-weight: 800; margin-top: 1rem;">No Account Selected</h3>
                <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">Please choose an account from the filter dropdown above to render the General Ledger.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
