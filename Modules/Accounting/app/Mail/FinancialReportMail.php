<?php

namespace Modules\Accounting\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FinancialReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $reportTitle,
        public string $period,
        public string $filenameBase,
        public array $formats = ['pdf', 'excel'],
        public ?string $pdfBase64 = null,
        public ?string $excelBase64 = null,
        public ?string $tenantName = null,
    ) {}

    public function envelope(): Envelope
    {
        $formatList = strtoupper(implode(' & ', $this->formats));

        return new Envelope(
            subject: "Financial Report: {$this->reportTitle} ({$this->period}) [{$formatList}]",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'accounting::reports.emails.report',
            with: [
                'reportTitle' => $this->reportTitle,
                'period'      => $this->period,
                'formats'     => $this->formats,
                'tenantName'  => $this->tenantName ?? config('app.name', 'Accounting System'),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if (in_array('pdf', $this->formats, true) && ! empty($this->pdfBase64)) {
            $attachments[] = Attachment::fromData(
                fn () => base64_decode($this->pdfBase64),
                "{$this->filenameBase}.pdf"
            )->withMime('application/pdf');
        }

        if (in_array('excel', $this->formats, true) && ! empty($this->excelBase64)) {
            $attachments[] = Attachment::fromData(
                fn () => base64_decode($this->excelBase64),
                "{$this->filenameBase}.xlsx"
            )->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        return $attachments;
    }
}
