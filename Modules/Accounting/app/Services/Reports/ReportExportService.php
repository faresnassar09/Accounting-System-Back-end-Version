<?php

namespace Modules\Accounting\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounting\Mail\FinancialReportMail;
use Symfony\Component\HttpFoundation\Response;

class ReportExportService
{
    /**
     * Generate and download a PDF report from a Blade view.
     *
     * @param string $view Blade view path (e.g., 'accounting::reports.pdf.trial-balance')
     * @param array $data View data variables
     * @param string $filename Base filename without extension
     * @param string $paper Paper size ('a4', 'letter', etc.)
     * @param string $orientation Paper orientation ('portrait' or 'landscape')
     * @return Response
     */
    public function exportPdf(
        string $view,
        array $data,
        string $filename,
        string $paper = 'a4',
        string $orientation = 'portrait'
    ): Response {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'sans-serif',
            ]);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Generate and download an Excel spreadsheet report.
     *
     * @param object $export Export object implementing FromView or FromArray
     * @param string $filename Base filename without extension
     * @return Response
     */
    public function exportExcel(object $export, string $filename): Response
    {
        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Generate raw binary PDF content in memory.
     */
    public function generatePdfContent(
        string $view,
        array $data,
        string $paper = 'a4',
        string $orientation = 'portrait'
    ): string {
        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'sans-serif',
            ])
            ->output();
    }

    /**
     * Generate raw binary Excel content in memory.
     */
    public function generateExcelContent(object $export): string
    {
        return Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Resolve requested attachments from request flexibly.
     * Supports:
     * - Comma-separated string: "pdf,excel" or "pdf"
     * - Array: ["pdf", "excel"]
     * - Parameter aliases: "attachments" or "attach"
     * - Intelligent fallback: if export=pdf, defaults to ["pdf"], else defaults to ["pdf", "excel"]
     *
     * @param Request $request
     * @param string|null $export
     * @return array
     */
    public function resolveAttachments(Request $request, ?string $export = null): array
    {
        $input = $request->input('attachments') ?? $request->input('attach');

        if (is_string($input)) {
            $input = explode(',', $input);
        }

        if (is_array($input) && ! empty($input)) {
            $normalized = array_map('trim', array_map('strtolower', $input));
            $valid = array_intersect($normalized, ['pdf', 'excel']);
            if (! empty($valid)) {
                return array_values(array_unique($valid));
            }
        }

        // If direct export was specified, default attachment to that format
        if (in_array($export, ['pdf', 'excel'], true)) {
            return [$export];
        }

        // Default to both formats for maximum flexibility
        return ['pdf', 'excel'];
    }

    /**
     * Send email with user-selected report attachments (PDF, Excel, or both).
     *
     * @param string $recipientEmail
     * @param string $reportTitle
     * @param string $period
     * @param string $filenameBase
     * @param string $view
     * @param array $viewData
     * @param object $exportObject
     * @param array $formats Formats to include: ['pdf'], ['excel'], or ['pdf', 'excel']
     * @param bool $queue Whether to dispatch via queue
     */
    public function sendReportMail(
        string $recipientEmail,
        string $reportTitle,
        string $period,
        string $filenameBase,
        string $view,
        array $viewData,
        object $exportObject,
        array $formats = ['pdf', 'excel'],
        bool $queue = true
    ): void {
        $formats = array_values(array_intersect(array_map('strtolower', $formats), ['pdf', 'excel']));
        if (empty($formats)) {
            $formats = ['pdf', 'excel'];
        }

        // Performance optimization & Queue safety:
        // Compile binary content only for requested formats and base64-encode for safe Queue JSON serialization
        $pdfBase64 = in_array('pdf', $formats, true) ? base64_encode($this->generatePdfContent($view, $viewData)) : null;
        $excelBase64 = in_array('excel', $formats, true) ? base64_encode($this->generateExcelContent($exportObject)) : null;
        $tenantName = tenancy()->tenant?->id ?? config('app.name', 'Accounting System');

        $mailable = new FinancialReportMail(
            reportTitle: $reportTitle,
            period: $period,
            filenameBase: $filenameBase,
            formats: $formats,
            pdfBase64: $pdfBase64,
            excelBase64: $excelBase64,
            tenantName: $tenantName,
        );

        if ($queue) {
            Mail::to($recipientEmail)->queue($mailable);
        } else {
            Mail::to($recipientEmail)->send($mailable);
        }
    }
}
