<table>
    <thead>
        <tr>
            <th colspan="6" style="font-weight: bold; font-size: 14px;">{{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}</th>
        </tr>
        <tr>
            <th colspan="6" style="font-weight: bold; font-size: 13px;">TRIAL BALANCE</th>
        </tr>
        <tr>
            <th colspan="6" style="color: #64748b;">As of: {{ $endDate ?? $data['endDate'] ?? now()->format('Y-m-d') }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <th>A/C #</th>
            <th>Account Name</th>
            <th style="text-align: right;">Period Debit</th>
            <th style="text-align: right;">Period Credit</th>
            <th style="text-align: right;">Final Debit</th>
            <th style="text-align: right;">Final Credit</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['reportData'] ?? $data['accounts'] ?? [] as $account)
            @php
                $acc = is_array($account) ? (object) $account : $account;
            @endphp
            <tr>
                <td>{{ $acc->number ?? $acc->id }}</td>
                <td>{{ $acc->name }}</td>
                <td style="text-align: right;">{{ number_format($acc->period_debit ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($acc->period_credit ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($acc->final_debit_balance ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($acc->final_credit_balance ?? 0, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center;">No transactions recorded for this period.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="4" style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">{{ number_format($data['totals']['total_debit'] ?? 0, 2) }}</td>
            <td style="text-align: right;">{{ number_format($data['totals']['total_credit'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: right; font-weight: bold;">
                Status: {{ (($data['totals']['isBalanced'] ?? false) == true) ? 'Balanced' : 'Out of Balance' }}
            </td>
        </tr>
    </tfoot>
</table>
