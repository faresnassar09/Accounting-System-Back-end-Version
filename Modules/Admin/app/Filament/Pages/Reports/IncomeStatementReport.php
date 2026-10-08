<?php

namespace Modules\Admin\Filament\Pages\Reports;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounting\Exports\IncomeStatementExport;
use Modules\Accounting\Services\Reports\IncomeStatementService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Branch\Models\Branch;
use BackedEnum;
use UnitEnum;

class IncomeStatementReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 3;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Income Statement (P&L)';

    protected static ?string $title = 'Income Statement (Profit & Loss)';

    protected static ?string $slug = 'reports/income-statement';

    protected string $view = 'admin::filament.pages.reports.income-statement';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $branchId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->branchId = null;
    }

    public function getReportData(): array
    {
        return app(IncomeStatementService::class)->generateReport(
            startDate: $this->startDate,
            endDate: $this->endDate,
            branchId: $this->branchId ? (int) $this->branchId : null
        );
    }

    public function getBranchesProperty()
    {
        return Branch::query()->where('active', 1)->orderBy('name')->pluck('name', 'id');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $reportData = $this->getReportData();
                    $content = app(ReportExportService::class)->generatePdfContent(
                        view: 'accounting::reports.pdf.income-statement',
                        data: [
                            'data'      => $reportData,
                            'startDate' => $this->startDate,
                            'endDate'   => $this->endDate,
                        ],
                    );
                    $filename = 'income_statement_' . ($this->endDate ?? now()->format('Y-m-d')) . '.pdf';

                    return response()->streamDownload(
                        fn () => print($content),
                        $filename,
                        ['Content-Type' => 'application/pdf']
                    );
                }),

            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $reportData = $this->getReportData();
                    $export = new IncomeStatementExport($reportData, $this->startDate, $this->endDate);
                    $filename = 'income_statement_' . ($this->endDate ?? now()->format('Y-m-d')) . '.xlsx';

                    return Excel::download($export, $filename);
                }),

            Action::make('email_report')
                ->label('Email Report')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->form([
                    TextInput::make('email')
                        ->label('Recipient Email')
                        ->email()
                        ->required()
                        ->default(fn () => auth()->user()?->email),
                    Select::make('format')
                        ->label('Report Format')
                        ->options([
                            'pdf'   => 'PDF Only',
                            'excel' => 'Excel Only (.xlsx)',
                            'both'  => 'Both (PDF & Excel)',
                        ])
                        ->default('both')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $formats = match ($data['format']) {
                        'pdf'   => ['pdf'],
                        'excel' => ['excel'],
                        default => ['pdf', 'excel'],
                    };

                    app(ReportExportService::class)->dispatchReportJob(
                        reportType: 'income-statement',
                        parameters: [
                            'startDate' => $this->startDate,
                            'endDate'   => $this->endDate,
                            'branch_id' => $this->branchId ? (int) $this->branchId : null,
                        ],
                        recipientEmail: $data['email'],
                        formats: $formats,
                    );

                    Notification::make()
                        ->title('Report Generation Queued')
                        ->body("Income Statement report will be emailed to {$data['email']} shortly.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
