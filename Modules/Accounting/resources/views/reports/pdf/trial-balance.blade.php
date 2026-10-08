@extends('accounting::reports.pdf.layout')

@section('title', 'Trial Balance')
@section('report_title', 'TRIAL BALANCE')
@section('report_period', 'As of: ' . ($endDate ?? $data['endDate'] ?? now()->format('Y-m-d')))

@section('content')

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">A/C #</th>
                <th style="width: 26%;">Account Name</th>
                <th class="text-right" style="width: 16%;">Period Debit</th>
                <th class="text-right" style="width: 16%;">Period Credit</th>
                <th class="text-right" style="width: 16%;">Final Debit</th>
                <th class="text-right" style="width: 16%;">Final Credit</th>
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
                    <td class="text-right">{{ number_format($acc->period_debit ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($acc->period_credit ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($acc->final_debit_balance ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($acc->final_credit_balance ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No transactions recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right">{{ number_format($data['totals']['total_debit'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($data['totals']['total_credit'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right" style="padding-top: 6px;">
                    Status: 
                    @if(($data['totals']['isBalanced'] ?? false) == true)
                        <span class="badge-success">Balanced &check;</span>
                    @else
                        <span class="badge-danger">Out of Balance &cross;</span>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

@endsection
