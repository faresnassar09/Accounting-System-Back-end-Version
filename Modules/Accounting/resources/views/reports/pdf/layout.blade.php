<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Financial Report')</title>
    <style>
        @page {
            margin: 25px 30px 40px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 15px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 4px;
        }
        .report-meta {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
        }
        .meta-table td {
            font-size: 10px;
            color: #475569;
        }
        .meta-table strong {
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        table.data-table td {
            padding: 5px 8px;
            font-size: 10px;
            border: 1px solid #e2e8f0;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table tr.total-row td {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-success {
            color: #15803d;
            font-weight: bold;
        }
        .badge-danger {
            color: #b91c1c;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 20px;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td class="text-left" style="width: 65%;">
                <div class="company-name">{{ tenancy()->tenant?->id ?? config('app.name', 'Accounting System') }}</div>
                <div class="report-title">@yield('report_title')</div>
                <div class="report-meta">@yield('report_period')</div>
            </td>
            <td class="text-right" style="width: 35%; vertical-align: top;">
                <div class="report-meta">Generated: {{ now()->format('Y-m-d H:i') }}</div>
                <div class="report-meta">Currency: {{ config('accounting.currency', 'USD') }}</div>
            </td>
        </tr>
    </table>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        Confidential Financial Document &bull; Generated automatically by Enterprise Accounting System
    </div>

</body>
</html>
