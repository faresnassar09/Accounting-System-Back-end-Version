@extends('accounting::reports.pdf.layout')

@section('title', 'Balance Sheet')
@section('report_title', 'BALANCE SHEET')
@section('report_period', 'As of: ' . ($endDate ?? now()->format('Y-m-d')))

@section('content')
    @php
        $assets = $data['assets_group'] ?? $data[0] ?? [];
        $liabilitiesEquity = $data['liabilities_and_equity_group'] ?? $data[1] ?? [];
        $assetsTotal = round((float)($assets['group_total'] ?? 0), 2);
        $liabEquityTotal = round((float)($liabilitiesEquity['group_total'] ?? 0), 2);
        $isBalanced = ($assetsTotal === $liabEquityTotal);
    @endphp

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Account / Category</th>
                <th class="text-right" style="width: 30%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <!-- ================= ASSETS ================= -->
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="2" style="background-color: #0f172a; color: #ffffff; font-size: 11px;">1. ASSETS</td>
            </tr>

            @foreach($assets['sub_types'] ?? [] as $subType)
                <tr style="background-color: #f1f5f9; font-weight: 600;">
                    <td colspan="2">{{ $subType['type_name'] ?? 'Asset Category' }}</td>
                </tr>
                @forelse($subType['accounts'] ?? [] as $account)
                    @php $acc = is_array($account) ? (object) $account : $account; @endphp
                    <tr>
                        <td style="padding-left: 20px;">{{ $acc->name ?? 'Account' }}</td>
                        <td class="text-right">{{ number_format($acc->netBalance ?? $acc->netbalance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No accounts recorded</td>
                        <td class="text-right">-</td>
                    </tr>
                @endforelse
                <tr style="font-weight: 600; color: #334155;">
                    <td style="padding-left: 15px;">Total {{ $subType['type_name'] ?? 'Category' }}</td>
                    <td class="text-right">{{ number_format($subType['type_total'] ?? 0, 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-row" style="background-color: #e0f2fe; font-size: 11px;">
                <td>TOTAL ASSETS</td>
                <td class="text-right">{{ number_format($assets['group_total'] ?? 0, 2) }}</td>
            </tr>

            <!-- Spacer Row -->
            <tr>
                <td colspan="2" style="border: none; padding: 6px 0;"></td>
            </tr>

            <!-- ================= LIABILITIES & EQUITY ================= -->
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="2" style="background-color: #0f172a; color: #ffffff; font-size: 11px;">2. LIABILITIES &amp; EQUITY</td>
            </tr>

            @foreach($liabilitiesEquity['sub_types'] ?? [] as $subType)
                <tr style="background-color: #f1f5f9; font-weight: 600;">
                    <td colspan="2">{{ $subType['type_name'] ?? 'Category' }}</td>
                </tr>
                @forelse($subType['accounts'] ?? [] as $account)
                    @php $acc = is_array($account) ? (object) $account : $account; @endphp
                    <tr>
                        <td style="padding-left: 20px;">{{ $acc->name ?? 'Account' }}</td>
                        <td class="text-right">{{ number_format($acc->netBalance ?? $acc->netbalance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No accounts recorded</td>
                        <td class="text-right">-</td>
                    </tr>
                @endforelse
                <tr style="font-weight: 600; color: #334155;">
                    <td style="padding-left: 15px;">Total {{ $subType['type_name'] ?? 'Category' }}</td>
                    <td class="text-right">{{ number_format($subType['type_total'] ?? 0, 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-row" style="background-color: #e0f2fe; font-size: 11px;">
                <td>TOTAL LIABILITIES &amp; EQUITY</td>
                <td class="text-right">{{ number_format($liabilitiesEquity['group_total'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="padding-top: 8px;">
                    Balance Sheet Status:
                    @if($isBalanced)
                        <span class="badge-success">Balanced (Assets = Liabilities + Equity)</span>
                    @else
                        <span class="badge-danger">Unbalanced Difference: {{ number_format(abs($assetsTotal - $liabEquityTotal), 2) }}</span>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

@endsection
