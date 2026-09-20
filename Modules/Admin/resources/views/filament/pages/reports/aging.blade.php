<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

    @php
        $report = $this->getReportData();
        $grand = $report['grand_total'];
    @endphp

    {{-- Controls Bar --}}
    <div class="fr-card">
        <div class="fr-filter-grid">
            <div>
                <label class="fr-filter-lbl">Report Ledger Perspective</label>
                <select wire:model.live="type" class="fr-input">
                    <option value="receivable">Accounts Receivable (Customer AR Aging)</option>
                    <option value="payable">Accounts Payable (Vendor AP Aging)</option>
                </select>
            </div>
            <div>
                <label class="fr-filter-lbl">Evaluation Cutoff Date</label>
                <input type="date" wire:model.live="asOfDate" class="fr-input" />
            </div>
            <div>
                <label class="fr-filter-lbl">Branch Location</label>
                <select wire:model.live="branchId" class="fr-input">
                    <option value="">Consolidated (All Branches)</option>
                    @foreach($this->branches as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Executive Exposure KPI Cards --}}
    <div class="fr-kpi-grid">
        <div class="fr-kpi-card fr-kpi-card-gray">
            <div class="fr-kpi-lbl">Current (0 - 30 Days)</div>
            <div class="fr-kpi-val">${{ number_format($grand['days_1_30'] + $grand['current'], 2) }}</div>
            <div class="fr-kpi-sub">Standard terms</div>
        </div>
        <div class="fr-kpi-card fr-kpi-card-blue">
            <div class="fr-kpi-lbl">31 - 60 Days Overdue</div>
            <div class="fr-kpi-val">${{ number_format($grand['days_31_60'], 2) }}</div>
            <div class="fr-kpi-sub">Follow-up advised</div>
        </div>
        <div class="fr-kpi-card fr-kpi-card-amber">
            <div class="fr-kpi-lbl">61 - 90 Days Overdue</div>
            <div class="fr-kpi-val">${{ number_format($grand['days_61_90'], 2) }}</div>
            <div class="fr-kpi-sub">Delinquent window</div>
        </div>
        <div class="fr-kpi-card fr-kpi-card-red">
            <div class="fr-kpi-lbl">90+ Days Critical</div>
            <div class="fr-kpi-val">${{ number_format($grand['days_over_90'], 2) }}</div>
            <div class="fr-kpi-sub">High default exposure</div>
        </div>
        <div class="fr-kpi-card fr-kpi-card-green">
            <div class="fr-kpi-lbl">Total Outstanding Balance</div>
            <div class="fr-kpi-val">${{ number_format($grand['total'], 2) }}</div>
            <div class="fr-kpi-sub">Net exposure</div>
        </div>
    </div>

    {{-- Aging Breakdown Table --}}
    <div class="fr-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--fr-border); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; font-size: 1.1rem;">{{ $report['type_label'] }}</div>
            <div style="font-size: 0.85rem; color: var(--fr-text-muted);">Cutoff: {{ $report['as_of_date'] }}</div>
        </div>
        <div class="fr-table-wrap">
            <table class="fr-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Account / Category</th>
                        <th style="text-align: right;">Current</th>
                        <th style="text-align: right;">1 - 30 Days</th>
                        <th style="text-align: right;">31 - 60 Days</th>
                        <th style="text-align: right;">61 - 90 Days</th>
                        <th style="text-align: right;">90+ Days</th>
                        <th style="text-align: right;">Total Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $row)
                        <tr>
                            <td style="text-align: left; font-weight: 600;">
                                #{{ $row['account_number'] }} — {{ $row['account_name'] }}
                                <span style="font-size: 0.75rem; color: #888; margin-left: 6px;">({{ $row['branch_name'] }})</span>
                            </td>
                            <td style="text-align: right;">${{ number_format($row['current'], 2) }}</td>
                            <td style="text-align: right;">${{ number_format($row['days_1_30'], 2) }}</td>
                            <td style="text-align: right; {{ $row['days_31_60'] > 0 ? 'color: #d97706; font-weight: 600;' : '' }}">
                                ${{ number_format($row['days_31_60'], 2) }}
                            </td>
                            <td style="text-align: right; {{ $row['days_61_90'] > 0 ? 'color: #ea580c; font-weight: 600;' : '' }}">
                                ${{ number_format($row['days_61_90'], 2) }}
                            </td>
                            <td style="text-align: right; {{ $row['days_over_90'] > 0 ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                ${{ number_format($row['days_over_90'], 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--fr-text-primary);">
                                ${{ number_format($row['total'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--fr-text-muted); padding: 3rem 1rem;">
                                No outstanding transactions found as of {{ $report['as_of_date'] }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: var(--fr-bg-subtle); font-weight: 700;">
                        <td style="text-align: left;">Grand Total</td>
                        <td style="text-align: right;">${{ number_format($grand['current'], 2) }}</td>
                        <td style="text-align: right;">${{ number_format($grand['days_1_30'], 2) }}</td>
                        <td style="text-align: right;">${{ number_format($grand['days_31_60'], 2) }}</td>
                        <td style="text-align: right;">${{ number_format($grand['days_61_90'], 2) }}</td>
                        <td style="text-align: right;">${{ number_format($grand['days_over_90'], 2) }}</td>
                        <td style="text-align: right; font-size: 1.05rem;">${{ number_format($grand['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>
