<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 24px;
            text-align: left;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .body {
            padding: 24px;
        }
        .report-card {
            background-color: #f1f5f9;
            border-left: 4px solid #2563eb;
            padding: 16px;
            border-radius: 4px;
            margin: 18px 0;
        }
        .report-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-card td {
            padding: 4px 0;
            font-size: 13px;
        }
        .report-card td.label {
            color: #64748b;
            width: 35%;
        }
        .report-card td.value {
            font-weight: 600;
            color: #0f172a;
        }
        .attachments-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0;
        }
        .attachments-box h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #334155;
            text-transform: uppercase;
        }
        .attachment-item {
            display: flex;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
            color: #0f172a;
        }
        .footer {
            padding: 16px 24px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $tenantName }}</h1>
            <p>Automated Financial Intelligence &amp; Reporting</p>
        </div>
        <div class="body">
            <p style="font-size: 14px; margin-top: 0;">Hello,</p>
            <p style="font-size: 14px;">The requested financial report has been compiled and is ready for your review.</p>

            <div class="report-card">
                <table>
                    <tr>
                        <td class="label">Report:</td>
                        <td class="value">{{ $reportTitle }}</td>
                    </tr>
                    <tr>
                        <td class="label">Period:</td>
                        <td class="value">{{ $period }}</td>
                    </tr>
                    <tr>
                        <td class="label">Generated:</td>
                        <td class="value">{{ now()->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value" style="color: #15803d;">Verified &amp; Exported</td>
                    </tr>
                </table>
            </div>

            <div class="attachments-box">
                @php $fmtCount = count($formats ?? ['pdf', 'excel']); @endphp
                <h3>Included {{ $fmtCount === 1 ? 'Attachment (1 File)' : 'Attachments (' . $fmtCount . ' Files)' }}:</h3>
                @if(in_array('pdf', $formats ?? ['pdf', 'excel']))
                    <div class="attachment-item">
                        &bull; <strong>PDF Document</strong> (.pdf) &mdash; Formatted for print, audit, and presentation.
                    </div>
                @endif
                @if(in_array('excel', $formats ?? ['pdf', 'excel']))
                    <div class="attachment-item">
                        &bull; <strong>Excel Spreadsheet</strong> (.xlsx) &mdash; Complete numerical dataset for spreadsheet analysis.
                    </div>
                @endif
            </div>

            <p style="font-size: 13px; color: #64748b;">
                The requested financial {{ $fmtCount === 1 ? 'report has' : 'reports have' }} been attached directly to this email for your convenience.
            </p>
        </div>
        <div class="footer">
            CONFIDENTIAL FINANCIAL DOCUMENT &bull; Generated by Enterprise Accounting System<br>
            Please do not forward this email to unauthorized personnel.
        </div>
    </div>
</body>
</html>
