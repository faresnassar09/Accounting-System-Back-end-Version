@extends('accounting::reports.pdf.layout')

@section('title', $report['type_label'])

@section('header')
    <h2>{{ $report['type_label'] }}</h2>
    <p class="subtitle">As of {{ $report['as_of_date'] }}</p>
@endsection

@section('content')
    <table class="report-table">
        <thead>
            <tr>
                <th style="text-align: left;">Account / Debtor</th>
                <th style="text-align: right;">Current</th>
                <th style="text-align: right;">1 - 30 Days</th>
                <th style="text-align: right;">31 - 60 Days</th>
                <th style="text-align: right;">61 - 90 Days</th>
                <th style="text-align: right;">90+ Days</th>
                <th style="text-align: right;">Total Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $row)
                <tr>
                    <td style="text-align: left;">#{{ $row['account_number'] }} — {{ $row['account_name'] }}</td>
                    <td style="text-align: right;">${{ number_format($row['current'], 2) }}</td>
                    <td style="text-align: right;">${{ number_format($row['days_1_30'], 2) }}</td>
                    <td style="text-align: right;">${{ number_format($row['days_31_60'], 2) }}</td>
                    <td style="text-align: right;">${{ number_format($row['days_61_90'], 2) }}</td>
                    <td style="text-align: right;">${{ number_format($row['days_over_90'], 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">${{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #888;">No outstanding balances found for this cutoff date.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f3f4f6;">
                <td style="text-align: left;">Grand Total</td>
                <td style="text-align: right;">${{ number_format($report['grand_total']['current'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($report['grand_total']['days_1_30'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($report['grand_total']['days_31_60'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($report['grand_total']['days_61_90'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($report['grand_total']['days_over_90'], 2) }}</td>
                <td style="text-align: right;">${{ number_format($report['grand_total']['total'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
