@extends('accounting::reports.pdf.layout')

@section('title', 'General Ledger')
@section('report_title', 'GENERAL LEDGER STATEMENT')
@section('report_period', 'Period: ' . ($startDate ?? 'Start') . ' to ' . ($endDate ?? now()->format('Y-m-d')))

@section('content')

    <table class="meta-table">
        <tr>
            <td style="width: 25%;"><strong>Account:</strong> {{ $data['account_info']['name'] ?? 'N/A' }} ({{ $data['account_info']['number'] ?? 'N/A' }})</td>
            <td style="width: 25%;"><strong>Opening Balance:</strong> {{ number_format($data['opening_balance'] ?? 0, 2) }}</td>
            <td style="width: 25%;"><strong>Closing Balance:</strong> {{ number_format($data['closing_balance'] ?? 0, 2) }}</td>
            <td style="width: 25%;" class="text-right"><strong>Net Movement:</strong> {{ number_format(($data['total_debit'] ?? 0) - ($data['total_credit'] ?? 0), 2) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">Date</th>
                <th style="width: 16%;">Reference</th>
                <th style="width: 34%;">Description</th>
                <th class="text-right" style="width: 12%;">Debit</th>
                <th class="text-right" style="width: 12%;">Credit</th>
                <th class="text-right" style="width: 12%;">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"><em>Opening Balance</em></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>{{ number_format($data['opening_balance'] ?? 0, 2) }}</strong></td>
            </tr>
            @forelse($data['transactions'] ?? [] as $tx)
                @php
                    $t = is_array($tx) ? (object) $tx : $tx;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($t->date)->format('Y-m-d') }}</td>
                    <td>{{ $t->reference }}</td>
                    <td>{{ $t->description }}</td>
                    <td class="text-right">{{ (float)$t->debit > 0 ? number_format($t->debit, 2) : '-' }}</td>
                    <td class="text-right">{{ (float)$t->credit > 0 ? number_format($t->credit, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($t->running_balance, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No movements for this account in the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL PERIOD MOVEMENTS:</td>
                <td class="text-right">{{ number_format($data['total_debit'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($data['total_credit'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($data['closing_balance'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

@endsection
