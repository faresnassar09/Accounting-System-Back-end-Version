@extends('accounting::reports.pdf.layout')

@section('title', 'Statement of Cash Flows')
@section('report_title', 'STATEMENT OF CASH FLOWS (INDIRECT METHOD)')
@section('report_period', 'Period: ' . ($data['start_date'] ?? '') . ' to ' . ($data['end_date'] ?? ''))

@section('content')
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

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Cash Flow Activity &amp; Line Item</th>
                <th class="text-right" style="width: 30%;">Amount (USD)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. CASH FLOWS FROM OPERATING ACTIVITIES -->
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="2" style="background-color: #0f172a; color: #ffffff; font-size: 11px;">
                    1. CASH FLOWS FROM OPERATING ACTIVITIES
                </td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Net Income (Profit / Loss for Period)</td>
                <td class="text-right" style="font-weight: 700;">{{ number_format($netIncome, 2) }}</td>
            </tr>
            @if($depreciation > 0)
                <tr>
                    <td style="padding-left: 20px;">Adjustments: Depreciation &amp; Amortization (Non-Cash)</td>
                    <td class="text-right">{{ number_format($depreciation, 2) }}</td>
                </tr>
            @endif

            <tr style="background-color: #f8fafc; font-weight: 600; font-size: 9px; text-transform: uppercase;">
                <td colspan="2" style="padding-left: 15px; color: #64748b;">Changes in Operating Assets &amp; Liabilities (Working Capital):</td>
            </tr>

            @forelse($operatingAssets as $item)
                <tr>
                    <td style="padding-left: 25px;">
                        {{ $item['cash_effect'] < 0 ? '(Increase)' : 'Decrease' }} in {{ $item['account_name'] }}
                    </td>
                    <td class="text-right">{{ number_format($item['cash_effect'], 2) }}</td>
                </tr>
            @empty
            @endforelse

            @forelse($operatingLiabs as $item)
                <tr>
                    <td style="padding-left: 25px;">
                        {{ $item['cash_effect'] >= 0 ? 'Increase' : '(Decrease)' }} in {{ $item['account_name'] }}
                    </td>
                    <td class="text-right">{{ number_format($item['cash_effect'], 2) }}</td>
                </tr>
            @empty
            @endforelse

            <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1;">
                <td>Net Cash Flow Provided by / (Used in) Operating Activities</td>
                <td class="text-right" style="font-weight: 800; color: {{ $netOperating >= 0 ? '#059669' : '#e11d48' }};">
                    {{ number_format($netOperating, 2) }}
                </td>
            </tr>

            <!-- 2. CASH FLOWS FROM INVESTING ACTIVITIES -->
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="2" style="background-color: #0f172a; color: #ffffff; font-size: 11px;">
                    2. CASH FLOWS FROM INVESTING ACTIVITIES
                </td>
            </tr>
            @forelse($investingItems as $item)
                <tr>
                    <td style="padding-left: 20px;">
                        {{ $item['cash_effect'] < 0 ? 'Capital Expenditure / Purchase:' : 'Disposal / Inflow:' }} {{ $item['account_name'] }}
                    </td>
                    <td class="text-right">{{ number_format($item['cash_effect'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No capital asset purchases or disposals in this period</td>
                    <td class="text-right">-</td>
                </tr>
            @endforelse
            <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1;">
                <td>Net Cash Flow Provided by / (Used in) Investing Activities</td>
                <td class="text-right" style="font-weight: 800; color: {{ $netInvesting >= 0 ? '#059669' : '#e11d48' }};">
                    {{ number_format($netInvesting, 2) }}
                </td>
            </tr>

            <!-- 3. CASH FLOWS FROM FINANCING ACTIVITIES -->
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="2" style="background-color: #0f172a; color: #ffffff; font-size: 11px;">
                    3. CASH FLOWS FROM FINANCING ACTIVITIES
                </td>
            </tr>
            @forelse($financingItems as $item)
                <tr>
                    <td style="padding-left: 20px;">{{ $item['account_name'] }}</td>
                    <td class="text-right">{{ number_format($item['cash_effect'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No debt or equity financing transactions in this period</td>
                    <td class="text-right">-</td>
                </tr>
            @endforelse
            <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1;">
                <td>Net Cash Flow Provided by / (Used in) Financing Activities</td>
                <td class="text-right" style="font-weight: 800; color: {{ $netFinancing >= 0 ? '#059669' : '#e11d48' }};">
                    {{ number_format($netFinancing, 2) }}
                </td>
            </tr>

            <!-- 4. SUMMARY & CASH RECONCILIATION -->
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="2" style="background-color: #0f172a; color: #ffffff; font-size: 11px;">
                    4. CASH RECONCILIATION SUMMARY
                </td>
            </tr>
            <tr style="font-weight: bold; background-color: #e2e8f0;">
                <td>Net Increase / (Decrease) in Cash and Cash Equivalents</td>
                <td class="text-right" style="font-size: 11px;">{{ number_format($computedNetCash, 2) }}</td>
            </tr>
            <tr>
                <td>Cash and Cash Equivalents at Beginning of Period</td>
                <td class="text-right">{{ number_format($beginningCash, 2) }}</td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: 800; border-top: 2px solid #0f172a; border-bottom: 3px double #0f172a;">
                <td>Cash and Cash Equivalents at End of Period</td>
                <td class="text-right" style="font-size: 12px;">{{ number_format($endingCash, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px; padding: 10px; background-color: {{ $isBalanced ? '#f0fdf4' : '#fef2f2' }}; border: 1px solid {{ $isBalanced ? '#bbf7d0' : '#fecaca' }}; text-align: center; border-radius: 4px;">
        <span style="font-weight: bold; color: {{ $isBalanced ? '#15803d' : '#b91c1c' }};">
            {{ $isBalanced ? '&#10003; Statement of Cash Flows Reconciled & Verified Balanced' : '&#10007; Cash Flow Discrepancy Detected: $' . number_format($data['variance'] ?? 0, 2) }}
        </span>
    </div>
@endsection
