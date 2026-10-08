<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14px;">{{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}</th>
        </tr>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 13px;">GENERAL LEDGER - {{ $data['account_info']['name'] ?? 'Account' }} ({{ $data['account_info']['number'] ?? '' }})</th>
        </tr>
        <tr>
            <th colspan="7" style="color: #64748b;">Period: {{ $startDate ?? 'Beginning' }} to {{ $endDate ?? now()->format('Y-m-d') }}</th>
        </tr>
        <tr>
            <th colspan="7">Opening Balance: {{ number_format($data['opening_balance'] ?? 0, 2) }}</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <th>Date</th>
            <th>Ref #</th>
            <th>Source</th>
            <th>Description</th>
            <th style="text-align: right;">Debit</th>
            <th style="text-align: right;">Credit</th>
            <th style="text-align: right;">Balance</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['transactions'] ?? [] as $tx)
            @php $item = is_array($tx) ? (object) $tx : $tx; @endphp
            <tr>
                <td>{{ $item->date }}</td>
                <td>{{ $item->reference ?? '-' }}</td>
                <td>{{ ucfirst($item->source_type ?? '-') }} #{{ $item->source_reference ?? '' }}</td>
                <td>{{ $item->description ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($item->debit ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($item->credit ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($item->running_balance ?? $item->balance ?? 0, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center;">No transactions found for this account in the specified period.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="4" style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">{{ number_format($data['total_debit'] ?? 0, 2) }}</td>
            <td style="text-align: right;">{{ number_format($data['total_credit'] ?? 0, 2) }}</td>
            <td style="text-align: right;">{{ number_format($data['closing_balance'] ?? 0, 2) }}</td>
        </tr>
    </tfoot>
</table>
