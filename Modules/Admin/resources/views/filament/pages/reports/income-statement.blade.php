<x-filament-panels::page>
    @include('admin::filament.pages.reports.partials.report-styles')

    @php
        $reportData = $this->getReportData();
        $totalRevenue = (float) ($reportData['total_revenue'] ?? 0);
        $grossSales = (float) ($reportData['gross_sales'] ?? 0);
        $salesDeductions = (float) ($reportData['sales_deductions'] ?? 0);
        $netSales = (float) ($reportData['net_sales'] ?? ($grossSales - $salesDeductions));
        $operatingRevenue = (float) ($reportData['operating_revenue'] ?? 0);

        $grossSalesAccounts = $reportData['gross_sales_details'] ?? [];
        $salesDeductionsAccounts = $reportData['sales_deductions_details'] ?? [];
        $operatingRevenues = $reportData['operating_revenue_details'] ?? [];

        $totalCogs = (float) ($reportData['total_cogs'] ?? 0);
        $cogsAccounts = $reportData['cogs_details'] ?? [];

        $grossProfit = (float) ($reportData['gross_profit'] ?? ($totalRevenue - $totalCogs));

        $totalExpenses = (float) ($reportData['total_expenses'] ?? 0);
        $operatingExpenses = $reportData['operating_expenses_details'] ?? [];

        $operatingIncome = (float) ($reportData['operating_income'] ?? ($grossProfit - $totalExpenses));

        $taxExpenseTotal = (float) ($reportData['tax_expense_total'] ?? 0);
        $taxExpenses = $reportData['tax_expenses_details'] ?? [];

        $netIncome = (float) ($reportData['net_income'] ?? ($operatingIncome - $taxExpenseTotal));
        $isProfit = ($netIncome >= 0);
        $selectedBranch = $this->branchId ? ($this->branches[$this->branchId] ?? 'Branch #' . $this->branchId) : 'All Branches (Consolidated)';
        
        $startDateFormatted = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->format('M d, Y') : 'Start of Period';
        $endDateFormatted = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('M d, Y') : now()->format('M d, Y');
        $hasRevenues = count($grossSalesAccounts) > 0 || count($salesDeductionsAccounts) > 0 || count($operatingRevenues) > 0;
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
                    INCOME STATEMENT (PROFIT &amp; LOSS)
                </h1>
                <p class="fr-banner-subtitle">
                    Financial performance for the period <strong style="color: #ffffff;">{{ $startDateFormatted }}</strong> to <strong style="color: #ffffff;">{{ $endDateFormatted }}</strong>
                </p>
            </div>

            <div class="fr-banner-metrics">
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Currency</div>
                    <div class="fr-banner-card-val">USD ($)</div>
                </div>
                <div class="fr-banner-card">
                    <div class="fr-banner-card-lbl">Bottom Line Result</div>
                    @if($isProfit)
                        <div class="fr-banner-card-val" style="color: #34d399;">
                            Net Income (Profit)
                        </div>
                    @else
                        <div class="fr-banner-card-val" style="color: #f87171;">
                            Net Loss
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="fr-card">
            <div class="fr-filters-grid cols-4">
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
                        <span>Update Statement</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Executive KPI Metrics Cards --}}
        <div class="fr-kpi-grid cols-4">
            {{-- Total Revenue --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Total Revenue</span>
                    <div class="fr-kpi-icon-box fr-bg-emerald">
                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val fr-text-emerald">
                        ${{ number_format($totalRevenue, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Gross sales less deductions
                    </div>
                </div>
            </div>

            {{-- Gross Profit --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Gross Profit</span>
                    <div class="fr-kpi-icon-box fr-bg-indigo">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $grossProfit >= 0 ? '' : 'fr-text-rose' }}">
                        ${{ number_format($grossProfit, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Revenue less Cost of Sales
                    </div>
                </div>
            </div>

            {{-- Operating Expenses --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Operating Expenses</span>
                    <div class="fr-kpi-icon-box fr-bg-rose">
                        <x-filament::icon icon="heroicon-o-credit-card" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val" style="color: #e11d48;">
                        ${{ number_format($totalExpenses, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc">
                        Overhead &amp; operating costs
                    </div>
                </div>
            </div>

            {{-- Net Profit / Loss --}}
            <div class="fr-kpi-card">
                <div class="fr-kpi-header">
                    <span class="fr-kpi-title">Net Profit / (Loss)</span>
                    <div class="fr-kpi-icon-box {{ $isProfit ? 'fr-bg-emerald' : 'fr-bg-rose' }}">
                        <x-filament::icon icon="{{ $isProfit ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-circle' }}" class="h-5 w-5" style="width: 20px; height: 20px;" />
                    </div>
                </div>
                <div>
                    <div class="fr-kpi-val {{ $isProfit ? 'fr-text-emerald' : 'fr-text-rose' }}">
                        ${{ number_format($netIncome, 2) }} <span class="fr-kpi-curr">USD</span>
                    </div>
                    <div class="fr-kpi-desc {{ $isProfit ? 'fr-text-emerald' : 'fr-text-rose' }}" style="font-weight: 700;">
                        {{ $isProfit ? 'Net Income (Profit)' : 'Net Operating Loss' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Financial Statement Breakdown Document --}}
        <div class="fr-card" style="display: flex; flex-direction: column; gap: 2rem;">
            {{-- 1. REVENUES & SALES --}}
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="fr-tag" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-weight: 900;">1</span>
                        <h3 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                            Revenues &amp; Sales
                        </h3>
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Amount (USD)</span>
                </div>

                <div class="fr-table-responsive">
                    <table class="fr-table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Account Code</th>
                                <th>Account Title</th>
                                <th style="text-align: right; width: 200px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$hasRevenues)
                                <tr>
                                    <td colspan="3" style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                                        No revenue transactions recorded for this period.
                                    </td>
                                </tr>
                            @else
                                {{-- Gross Sales Accounts --}}
                                @if(count($grossSalesAccounts) > 0)
                                    <tr style="background: rgba(148, 163, 184, 0.06);">
                                        <td colspan="3" style="font-weight: 800; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                                            Gross Sales &amp; Billings
                                        </td>
                                    </tr>
                                    @foreach($grossSalesAccounts as $gs)
                                        @php
                                            $gsObj = is_array($gs) ? (object) $gs : $gs;
                                            $code = $gsObj->account_number ?? $gsObj->number ?? '';
                                            $name = $gsObj->name ?? $gsObj->account_name ?? 'Sales Account';
                                            $bal = (float) ($gsObj->balance ?? 0);
                                        @endphp
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 700; color: #f59e0b; padding-left: 1.5rem;">
                                                {{ $code ? '#' . $code : '—' }}
                                            </td>
                                            <td style="font-weight: 600;">{{ $name }}</td>
                                            <td class="fr-num" style="font-weight: 700;">
                                                ${{ number_format($bal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Sales Deductions --}}
                                @if(count($salesDeductionsAccounts) > 0)
                                    <tr style="background: rgba(244, 63, 94, 0.04);">
                                        <td colspan="3" style="font-weight: 800; font-size: 0.75rem; text-transform: uppercase; color: #e11d48;">
                                            Less: Sales Deductions &amp; Discounts
                                        </td>
                                    </tr>
                                    @foreach($salesDeductionsAccounts as $sd)
                                        @php
                                            $sdObj = is_array($sd) ? (object) $sd : $sd;
                                            $code = $sdObj->account_number ?? $sdObj->number ?? '';
                                            $name = $sdObj->name ?? $sdObj->account_name ?? 'Sales Deduction';
                                            $bal = (float) ($sdObj->balance ?? 0);
                                        @endphp
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 700; color: #f59e0b; padding-left: 1.5rem;">
                                                {{ $code ? '#' . $code : '—' }}
                                            </td>
                                            <td style="font-weight: 600; color: #e11d48;">{{ $name }}</td>
                                            <td class="fr-num" style="color: #e11d48; font-weight: 700;">
                                                ({{ number_format($bal, 2) }})
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Other Operating Revenues --}}
                                @if(count($operatingRevenues) > 0)
                                    <tr style="background: rgba(148, 163, 184, 0.06);">
                                        <td colspan="3" style="font-weight: 800; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                                            Other Operating Revenues
                                        </td>
                                    </tr>
                                    @foreach($operatingRevenues as $rev)
                                        @php
                                            $revObj = is_array($rev) ? (object) $rev : $rev;
                                            $code = $revObj->account_number ?? $revObj->number ?? '';
                                            $name = $revObj->name ?? $revObj->account_name ?? 'Revenue Account';
                                            $bal = (float) ($revObj->balance ?? 0);
                                        @endphp
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 700; color: #f59e0b; padding-left: 1.5rem;">
                                                {{ $code ? '#' . $code : '—' }}
                                            </td>
                                            <td style="font-weight: 600;">{{ $name }}</td>
                                            <td class="fr-num" style="font-weight: 700;">
                                                ${{ number_format($bal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                    Total Revenues (Net Sales)
                                </td>
                                <td class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #059669;">
                                    ${{ number_format($totalRevenue, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 2. COST OF GOODS SOLD (COGS) --}}
            @if(count($cogsAccounts) > 0 || $totalCogs > 0)
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="fr-tag" style="background: rgba(217, 119, 6, 0.15); color: #d97706; font-weight: 900;">2</span>
                            <h3 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                                Cost of Goods Sold (COGS)
                            </h3>
                        </div>
                        <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Amount (USD)</span>
                    </div>

                    <div class="fr-table-responsive">
                        <table class="fr-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Account Code</th>
                                    <th>Account Title</th>
                                    <th style="text-align: right; width: 200px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cogsAccounts as $cg)
                                    @php
                                        $cgObj = is_array($cg) ? (object) $cg : $cg;
                                        $code = $cgObj->account_number ?? $cgObj->number ?? '';
                                        $name = $cgObj->name ?? $cgObj->account_name ?? 'COGS Account';
                                        $bal = (float) ($cgObj->balance ?? 0);
                                    @endphp
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 700; color: #f59e0b;">
                                            {{ $code ? '#' . $code : '—' }}
                                        </td>
                                        <td style="font-weight: 600;">
                                            {{ $name }}
                                        </td>
                                        <td class="fr-num" style="color: #e11d48; font-weight: 700;">
                                            ({{ number_format($bal, 2) }})
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                        Total Cost of Goods Sold
                                    </td>
                                    <td class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #e11d48;">
                                        ({{ number_format($totalCogs, 2) }})
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Gross Profit Ribbon --}}
            <div style="background: rgba(79, 70, 229, 0.06); border: 1px solid rgba(79, 70, 229, 0.15); border-radius: 0.75rem; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5" style="width: 20px; height: 20px; color: #4f46e5;" />
                    <span style="font-weight: 800; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        Gross Profit
                    </span>
                </div>
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 1.25rem; font-weight: 900;">
                    ${{ number_format($grossProfit, 2) }} <span style="font-size: 0.75rem; color: #94a3b8;">USD</span>
                </div>
            </div>

            {{-- 3. OPERATING EXPENSES --}}
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="fr-tag" style="background: rgba(244, 63, 94, 0.15); color: #e11d48; font-weight: 900;">3</span>
                        <h3 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                            Operating Expenses
                        </h3>
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Amount (USD)</span>
                </div>

                <div class="fr-table-responsive">
                    <table class="fr-table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Account Code</th>
                                <th>Account Title</th>
                                <th style="text-align: right; width: 200px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operatingExpenses as $exp)
                                @php
                                    $expObj = is_array($exp) ? (object) $exp : $exp;
                                    $code = $expObj->account_number ?? $expObj->number ?? '';
                                    $name = $expObj->name ?? $expObj->account_name ?? 'Expense Account';
                                    $bal = (float) ($expObj->balance ?? 0);
                                @endphp
                                <tr>
                                    <td style="font-family: monospace; font-weight: 700; color: #f59e0b;">
                                        {{ $code ? '#' . $code : '—' }}
                                    </td>
                                    <td style="font-weight: 600;">
                                        {{ $name }}
                                    </td>
                                    <td class="fr-num" style="color: #e11d48; font-weight: 700;">
                                        ({{ number_format($bal, 2) }})
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                                        No operating expense accounts found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                    Total Operating Expenses
                                </td>
                                <td class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #e11d48;">
                                    ({{ number_format($totalExpenses, 2) }})
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 4. TAX EXPENSES (IF ANY) --}}
            @if(count($taxExpenses) > 0 || $taxExpenseTotal > 0)
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="fr-tag" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-weight: 900;">4</span>
                            <h3 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
                                Income Taxes
                            </h3>
                        </div>
                        <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Amount (USD)</span>
                    </div>

                    <div class="fr-table-responsive">
                        <table class="fr-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Account Code</th>
                                    <th>Account Title</th>
                                    <th style="text-align: right; width: 200px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($taxExpenses as $tx)
                                    @php
                                        $txObj = is_array($tx) ? (object) $tx : $tx;
                                        $code = $txObj->account_number ?? $txObj->number ?? '';
                                        $name = $txObj->name ?? $txObj->account_name ?? 'Tax Account';
                                        $bal = (float) ($txObj->balance ?? 0);
                                    @endphp
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 700; color: #f59e0b;">
                                            {{ $code ? '#' . $code : '—' }}
                                        </td>
                                        <td style="font-weight: 600;">
                                            {{ $name }}
                                        </td>
                                        <td class="fr-num" style="color: #e11d48; font-weight: 700;">
                                            ({{ number_format($bal, 2) }})
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
                                        Total Tax Expense
                                    </td>
                                    <td class="fr-num" style="font-size: 1.125rem; font-weight: 900; color: #e11d48;">
                                        ({{ number_format($taxExpenseTotal, 2) }})
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Grand Finale Net Income Card --}}
            <div style="border-radius: 1rem; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border: 2px solid {{ $isProfit ? 'rgba(16, 185, 129, 0.4)' : 'rgba(244, 63, 94, 0.4)' }}; background: {{ $isProfit ? 'rgba(16, 185, 129, 0.06)' : 'rgba(244, 63, 94, 0.06)' }};">
                <div>
                    <div style="font-size: 0.6875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; color: {{ $isProfit ? '#059669' : '#e11d48' }};">
                        Final Performance Summary
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 900; margin: 0.25rem 0 0; color: {{ $isProfit ? '#059669' : '#e11d48' }};">
                        {{ $isProfit ? 'Net Income (Profit)' : 'Net Loss' }}
                    </h2>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0;">
                        Consolidated statement result for period ending {{ $endDateFormatted }}
                    </p>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: #94a3b8;">
                        Net Result
                    </div>
                    <div style="margin-top: 0.25rem; border-bottom: 4px double {{ $isProfit ? '#059669' : '#e11d48' }}; padding-bottom: 0.25rem;">
                        <span class="fr-num" style="font-size: 2rem; font-weight: 900; color: {{ $isProfit ? '#059669' : '#e11d48' }};">
                            ${{ number_format($netIncome, 2) }}
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: {{ $isProfit ? '#059669' : '#e11d48' }};">USD</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
