<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px;">{{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 13px;">BALANCE SHEET</th>
        </tr>
        <tr>
            <th colspan="2" style="color: #64748b;">As of: {{ $endDate ?? now()->format('Y-m-d') }}</th>
        </tr>
        <tr>
            <th colspan="2"></th>
        </tr>
        <tr>
            <th style="background-color: #0f172a; color: #ffffff; font-weight: bold;">Account / Category</th>
            <th style="background-color: #0f172a; color: #ffffff; font-weight: bold; text-align: right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $assets = $data['assets_group'] ?? $data[0] ?? [];
            $liabilitiesEquity = $data['liabilities_and_equity_group'] ?? $data[1] ?? [];
            $assetsTotal = round((float)($assets['group_total'] ?? 0), 2);
            $liabEquityTotal = round((float)($liabilitiesEquity['group_total'] ?? 0), 2);
            $isBalanced = ($assetsTotal === $liabEquityTotal);
        @endphp

        <!-- 1. ASSETS -->
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="2">1. ASSETS</td>
        </tr>

        @foreach($assets['sub_types'] ?? [] as $subType)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">{{ $subType['type_name'] ?? 'Asset Category' }}</td>
            </tr>
            @forelse($subType['accounts'] ?? [] as $account)
                @php $acc = is_array($account) ? (object) $account : $account; @endphp
                <tr>
                    <td style="padding-left: 20px;">{{ $acc->name ?? 'Account' }}</td>
                    <td style="text-align: right;">{{ number_format($acc->netBalance ?? $acc->netbalance ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No accounts recorded</td>
                    <td style="text-align: right;">-</td>
                </tr>
            @endforelse
            <tr style="font-weight: bold;">
                <td>Total {{ $subType['type_name'] ?? 'Category' }}</td>
                <td style="text-align: right;">{{ number_format($subType['type_total'] ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr style="background-color: #e0f2fe; font-weight: bold;">
            <td>TOTAL ASSETS</td>
            <td style="text-align: right;">{{ number_format($assets['group_total'] ?? 0, 2) }}</td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>

        <!-- 2. LIABILITIES & EQUITY -->
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="2">2. LIABILITIES &amp; EQUITY</td>
        </tr>

        @foreach($liabilitiesEquity['sub_types'] ?? [] as $subType)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">{{ $subType['type_name'] ?? 'Category' }}</td>
            </tr>
            @forelse($subType['accounts'] ?? [] as $account)
                @php $acc = is_array($account) ? (object) $account : $account; @endphp
                <tr>
                    <td style="padding-left: 20px;">{{ $acc->name ?? 'Account' }}</td>
                    <td style="text-align: right;">{{ number_format($acc->netBalance ?? $acc->netbalance ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td style="padding-left: 20px; color: #94a3b8; font-style: italic;">No accounts recorded</td>
                    <td style="text-align: right;">-</td>
                </tr>
            @endforelse
            <tr style="font-weight: bold;">
                <td>Total {{ $subType['type_name'] ?? 'Category' }}</td>
                <td style="text-align: right;">{{ number_format($subType['type_total'] ?? 0, 2) }}</td>
            </tr>
        @endforeach

        <tr style="background-color: #e0f2fe; font-weight: bold;">
            <td>TOTAL LIABILITIES &amp; EQUITY</td>
            <td style="text-align: right;">{{ number_format($liabilitiesEquity['group_total'] ?? 0, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold;">
                Balance Sheet Status: {{ $isBalanced ? 'Balanced (Assets = Liabilities + Equity)' : 'Unbalanced Difference: ' . number_format(abs($assetsTotal - $liabEquityTotal), 2) }}
            </td>
        </tr>
    </tfoot>
</table>
