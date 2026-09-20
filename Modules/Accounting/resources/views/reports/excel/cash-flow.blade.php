<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px;">{{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 13px;">STATEMENT OF CASH FLOWS (INDIRECT METHOD)</th>
        </tr>
        <tr>
            <th colspan="2" style="color: #64748b;">Period: {{ $data['start_date'] ?? '' }} to {{ $data['end_date'] ?? '' }}</th>
        </tr>
        <tr>
            <th colspan="2"></th>
        </tr>
        <tr>
            <th style="background-color: #0f172a; color: #ffffff; font-weight: bold;">Cash Flow Activity &amp; Line Item</th>
            <th style="background-color: #0f172a; color: #ffffff; font-weight: bold; text-align: right;">Amount (USD)</th>
        </tr>
    </thead>
    <tbody>
        @php
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
        @endphp

        <!-- 1. OPERATING ACTIVITIES -->
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="2">1. CASH FLOWS FROM OPERATING ACTIVITIES</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Net Income (Profit / Loss for Period)</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($netIncome, 2) }}</td>
        </tr>
        @if($depreciation > 0)
            <tr>
                <td style="padding-left: 20px;">Adjustments: Depreciation &amp; Amortization (Non-Cash)</td>
                <td style="text-align: right;">{{ number_format($depreciation, 2) }}</td>
            </tr>
        @endif

        <tr style="background-color: #f8fafc; font-weight: bold;">
            <td colspan="2" style="color: #64748b;">Changes in Operating Assets &amp; Liabilities (Working Capital):</td>
        </tr>

        @foreach($operatingAssets as $item)
            <tr>
                <td style="padding-left: 25px;">{{ $item['cash_effect'] < 0 ? '(Increase)' : 'Decrease' }} in {{ $item['account_name'] }}</td>
                <td style="text-align: right;">{{ number_format($item['cash_effect'], 2) }}</td>
            </tr>
        @endforeach

        @foreach($operatingLiabs as $item)
            <tr>
                <td style="padding-left: 25px;">{{ $item['cash_effect'] >= 0 ? 'Increase' : '(Decrease)' }} in {{ $item['account_name'] }}</td>
                <td style="text-align: right;">{{ number_format($item['cash_effect'], 2) }}</td>
            </tr>
        @endforeach

        <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1;">
            <td>Net Cash Flow Provided by / (Used in) Operating Activities</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($netOperating, 2) }}</td>
        </tr>

        <!-- 2. INVESTING ACTIVITIES -->
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="2">2. CASH FLOWS FROM INVESTING ACTIVITIES</td>
        </tr>
        @forelse($investingItems as $item)
            <tr>
                <td style="padding-left: 20px;">{{ $item['cash_effect'] < 0 ? 'Capital Expenditure / Purchase:' : 'Disposal / Inflow:' }} {{ $item['account_name'] }}</td>
                <td style="text-align: right;">{{ number_format($item['cash_effect'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No capital asset purchases or disposals in this period</td>
                <td style="text-align: right;">-</td>
            </tr>
        @endforelse
        <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1;">
            <td>Net Cash Flow Provided by / (Used in) Investing Activities</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($netInvesting, 2) }}</td>
        </tr>

        <!-- 3. FINANCING ACTIVITIES -->
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="2">3. CASH FLOWS FROM FINANCING ACTIVITIES</td>
        </tr>
        @forelse($financingItems as $item)
            <tr>
                <td style="padding-left: 20px;">{{ $item['account_name'] }}</td>
                <td style="text-align: right;">{{ number_format($item['cash_effect'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No debt or equity financing transactions in this period</td>
                <td style="text-align: right;">-</td>
            </tr>
        @endforelse
        <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1;">
            <td>Net Cash Flow Provided by / (Used in) Financing Activities</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($netFinancing, 2) }}</td>
        </tr>

        <!-- 4. CASH RECONCILIATION SUMMARY -->
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="2">4. CASH RECONCILIATION SUMMARY</td>
        </tr>
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td>Net Increase / (Decrease) in Cash and Cash Equivalents</td>
            <td style="text-align: right;">{{ number_format($computedNetCash, 2) }}</td>
        </tr>
        <tr>
            <td>Cash and Cash Equivalents at Beginning of Period</td>
            <td style="text-align: right;">{{ number_format($beginningCash, 2) }}</td>
        </tr>
        <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #0f172a;">
            <td>Cash and Cash Equivalents at End of Period</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($endingCash, 2) }}</td>
        </tr>
    </tbody>
</table>
