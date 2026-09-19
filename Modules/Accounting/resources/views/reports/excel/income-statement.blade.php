<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px;">{{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 13px;">INCOME STATEMENT (PROFIT &amp; LOSS)</th>
        </tr>
        <tr>
            <th colspan="2" style="color: #64748b;">Period: {{ $startDate ?? 'Beginning' }} to {{ $endDate ?? now()->format('Y-m-d') }}</th>
        </tr>
        <tr>
            <th colspan="2"></th>
        </tr>
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <th>Category / Account</th>
            <th style="text-align: right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <!-- REVENUES -->
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="2">1. REVENUES</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Gross Sales</td>
            <td style="text-align: right;">{{ number_format($data['gross_sales'] ?? 0, 2) }}</td>
        </tr>
        @if(($data['sales_deductions'] ?? 0) > 0)
            <tr>
                <td style="padding-left: 20px;">Less: Sales Deductions &amp; Discounts</td>
                <td style="text-align: right;">({{ number_format($data['sales_deductions'], 2) }})</td>
            </tr>
        @endif
        <tr style="font-weight: bold;">
            <td style="padding-left: 20px;">Net Sales</td>
            <td style="text-align: right;">{{ number_format($data['net_sales'] ?? 0, 2) }}</td>
        </tr>
        @if(($data['operating_revenue'] ?? 0) > 0)
            <tr>
                <td style="padding-left: 20px;">Other Operating Revenue</td>
                <td style="text-align: right;">{{ number_format($data['operating_revenue'], 2) }}</td>
            </tr>
        @endif
        <tr style="font-weight: bold;">
            <td>TOTAL REVENUE</td>
            <td style="text-align: right;">{{ number_format($data['total_revenue'] ?? 0, 2) }}</td>
        </tr>

        <!-- COGS & GROSS PROFIT -->
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="2">2. COST OF GOODS SOLD</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">Cost of Goods Sold (COGS)</td>
            <td style="text-align: right;">({{ number_format($data['total_cogs'] ?? 0, 2) }})</td>
        </tr>
        <tr style="background-color: #e0f2fe; font-weight: bold;">
            <td>GROSS PROFIT</td>
            <td style="text-align: right;">{{ number_format($data['gross_profit'] ?? 0, 2) }}</td>
        </tr>

        <!-- OPERATING EXPENSES -->
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <td colspan="2">3. OPERATING EXPENSES</td>
        </tr>
        @forelse($data['operating_expenses_details'] ?? [] as $exp)
            @php $expObj = is_array($exp) ? (object) $exp : $exp; @endphp
            <tr>
                <td style="padding-left: 20px;">{{ $expObj->name ?? $expObj->account_name ?? 'Expense' }}</td>
                <td style="text-align: right;">{{ number_format($expObj->balance ?? 0, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td style="padding-left: 20px;">Total Operating Expenses</td>
                <td style="text-align: right;">{{ number_format($data['total_expenses'] ?? 0, 2) }}</td>
            </tr>
        @endforelse
        <tr style="font-weight: bold;">
            <td>TOTAL OPERATING EXPENSES</td>
            <td style="text-align: right;">({{ number_format($data['total_expenses'] ?? 0, 2) }})</td>
        </tr>
        <tr style="background-color: #e0f2fe; font-weight: bold;">
            <td>OPERATING INCOME</td>
            <td style="text-align: right;">{{ number_format($data['operating_income'] ?? 0, 2) }}</td>
        </tr>

        <!-- TAXES -->
        @if(($data['tax_expense_total'] ?? 0) > 0)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">4. TAX EXPENSE</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Income Tax</td>
                <td style="text-align: right;">({{ number_format($data['tax_expense_total'], 2) }})</td>
            </tr>
        @endif

        <!-- NET INCOME -->
        <tr style="background-color: #fef08a; font-weight: bold;">
            <td>NET INCOME ({{ ($data['net_income'] ?? 0) >= 0 ? 'NET PROFIT' : 'NET LOSS' }})</td>
            <td style="text-align: right;">{{ number_format($data['net_income'] ?? 0, 2) }}</td>
        </tr>
    </tbody>
</table>
