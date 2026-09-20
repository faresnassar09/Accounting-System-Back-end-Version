<table>
    <thead>
        <tr>
            <th colspan="7" style="font-size: 16px; font-weight: bold; text-align: center;">
                {{ $report['type_label'] }}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center; color: #666;">
                As of: {{ $report['as_of_date'] }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f2f2f2;">Account</th>
            <th style="font-weight: bold; background-color: #f2f2f2; text-align: right;">Current</th>
            <th style="font-weight: bold; background-color: #f2f2f2; text-align: right;">1 - 30 Days</th>
            <th style="font-weight: bold; background-color: #f2f2f2; text-align: right;">31 - 60 Days</th>
            <th style="font-weight: bold; background-color: #f2f2f2; text-align: right;">61 - 90 Days</th>
            <th style="font-weight: bold; background-color: #f2f2f2; text-align: right;">90+ Days</th>
            <th style="font-weight: bold; background-color: #f2f2f2; text-align: right;">Total Outstanding</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report['rows'] as $row)
            <tr>
                <td>#{{ $row['account_number'] }} — {{ $row['account_name'] }}</td>
                <td style="text-align: right;">{{ $row['current'] }}</td>
                <td style="text-align: right;">{{ $row['days_1_30'] }}</td>
                <td style="text-align: right;">{{ $row['days_31_60'] }}</td>
                <td style="text-align: right;">{{ $row['days_61_90'] }}</td>
                <td style="text-align: right;">{{ $row['days_over_90'] }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $row['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="font-weight: bold; background-color: #e5e7eb;">Grand Total</td>
            <td style="text-align: right; font-weight: bold; background-color: #e5e7eb;">{{ $report['grand_total']['current'] }}</td>
            <td style="text-align: right; font-weight: bold; background-color: #e5e7eb;">{{ $report['grand_total']['days_1_30'] }}</td>
            <td style="text-align: right; font-weight: bold; background-color: #e5e7eb;">{{ $report['grand_total']['days_31_60'] }}</td>
            <td style="text-align: right; font-weight: bold; background-color: #e5e7eb;">{{ $report['grand_total']['days_61_90'] }}</td>
            <td style="text-align: right; font-weight: bold; background-color: #e5e7eb;">{{ $report['grand_total']['days_over_90'] }}</td>
            <td style="text-align: right; font-weight: bold; background-color: #e5e7eb;">{{ $report['grand_total']['total'] }}</td>
        </tr>
    </tfoot>
</table>
