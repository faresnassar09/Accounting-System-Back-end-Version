@extends('accounting::reports.pdf.layout')

@section('title', 'Income Statement')
@section('report_title', 'INCOME STATEMENT (PROFIT & LOSS)')
@section('report_period', 'For Period: ' . ($startDate ?? 'Beginning') . ' to ' . ($endDate ?? now()->format('Y-m-d')))

@section('content')

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Category / Account</th>
                <th class="text-right" style="width: 30%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <!-- REVENUES -->
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">1. REVENUES</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Gross Sales</td>
                <td class="text-right">{{ number_format($data['gross_sales'] ?? 0, 2) }}</td>
            </tr>
            @if(($data['sales_deductions'] ?? 0) > 0)
                <tr>
                    <td style="padding-left: 20px; color: #dc2626;">Less: Sales Deductions & Discounts</td>
                    <td class="text-right" style="color: #dc2626;">({{ number_format($data['sales_deductions'], 2) }})</td>
                </tr>
            @endif
            <tr style="font-weight: 600;">
                <td style="padding-left: 20px;">Net Sales</td>
                <td class="text-right">{{ number_format($data['net_sales'] ?? 0, 2) }}</td>
            </tr>
            @if(($data['operating_revenue'] ?? 0) > 0)
                <tr>
                    <td style="padding-left: 20px;">Other Operating Revenue</td>
                    <td class="text-right">{{ number_format($data['operating_revenue'], 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL REVENUE</td>
                <td class="text-right">{{ number_format($data['total_revenue'] ?? 0, 2) }}</td>
            </tr>

            <!-- COGS & GROSS PROFIT -->
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">2. COST OF GOODS SOLD</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Cost of Goods Sold (COGS)</td>
                <td class="text-right">({{ number_format($data['total_cogs'] ?? 0, 2) }})</td>
            </tr>
            <tr class="total-row" style="background-color: #e0f2fe;">
                <td>GROSS PROFIT</td>
                <td class="text-right">{{ number_format($data['gross_profit'] ?? 0, 2) }}</td>
            </tr>

            <!-- OPERATING EXPENSES -->
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">3. OPERATING EXPENSES</td>
            </tr>
            @forelse($data['operating_expenses_details'] ?? [] as $exp)
                <tr>
                    <td style="padding-left: 20px;">{{ $exp['name'] ?? $exp['account_name'] ?? 'Expense' }}</td>
                    <td class="text-right">{{ number_format($exp['balance'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td style="padding-left: 20px;">Total Operating Expenses</td>
                    <td class="text-right">{{ number_format($data['total_expenses'] ?? 0, 2) }}</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td>TOTAL OPERATING EXPENSES</td>
                <td class="text-right">({{ number_format($data['total_expenses'] ?? 0, 2) }})</td>
            </tr>
            <tr class="total-row" style="background-color: #e0f2fe;">
                <td>OPERATING INCOME</td>
                <td class="text-right">{{ number_format($data['operating_income'] ?? 0, 2) }}</td>
            </tr>

            <!-- TAXES -->
            @if(($data['tax_expense_total'] ?? 0) > 0)
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="2">4. TAX EXPENSE</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Income Tax</td>
                    <td class="text-right">({{ number_format($data['tax_expense_total'], 2) }})</td>
                </tr>
            @endif

            <!-- NET INCOME -->
            <tr class="total-row" style="background-color: #fef08a; font-size: 12px;">
                <td><strong>NET INCOME ({{ ($data['net_income'] ?? 0) >= 0 ? 'NET PROFIT' : 'NET LOSS' }})</strong></td>
                <td class="text-right"><strong>{{ number_format($data['net_income'] ?? 0, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

@endsection
