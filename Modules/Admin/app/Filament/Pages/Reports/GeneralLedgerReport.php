<?php

namespace Modules\Admin\Filament\Pages\Reports;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounting\Exports\GeneralLedgerExport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\Reports\GeneralLedgerService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Branch\Models\Branch;
use BackedEnum;
use UnitEnum;

class GeneralLedgerReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Financial Reports';

    protected static ?int $navigationSort = 2;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'General Ledger';

    protected static ?string $title = 'General Ledger';

    protected static ?string $slug = 'reports/general-ledger';

    protected string $view = 'admin::filament.pages.reports.general-ledger';

    public ?int $accountId = null;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $branchId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->branchId = null;
        $this->accountId = Account::query()->first()?->id;
    }

    public function getReportData(): ?array
    {
        if (! $this->accountId) {
            return null;
        }

        return app(GeneralLedgerService::class)->generateReport([
            'accountId' => (int) $this->accountId,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
            'branch_id' => $this->branchId ? (int) $this->branchId : null,
        ]);
    }

    public function getAccountsProperty()
    {
        return Account::query()
            ->orderBy('number')
            ->get()
            ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->number} - {$acc->name}"]);
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
                ->visible(fn () => filled($this->accountId))
                ->action(function () {
                    $reportData = $this->getReportData();
                    if (! $reportData) {
                        return;
                    }

                    $content = app(ReportExportService::class)->generatePdfContent(
                        view: 'accounting::reports.pdf.general-ledger',
                        data: [
                            'data'      => $reportData,
                            'startDate' => $this->startDate,
                            'endDate'   => $this->endDate,
                        ],
                    );
                    $accNumber = $reportData['account_info']['number'] ?? 'account';
                    $filename = "general_ledger_{$accNumber}_" . ($this->endDate ?? now()->format('Y-m-d')) . '.pdf';

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
                ->visible(fn () => filled($this->accountId))
                ->action(function () {
                    $reportData = $this->getReportData();
                    if (! $reportData) {
                        return;
                    }

                    $export = new GeneralLedgerExport($reportData, $this->startDate, $this->endDate);
                    $accNumber = $reportData['account_info']['number'] ?? 'account';
                    $filename = "general_ledger_{$accNumber}_" . ($this->endDate ?? now()->format('Y-m-d')) . '.xlsx';

                    return Excel::download($export, $filename);
                }),

            Action::make('email_report')
                ->label('Email Report')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->visible(fn () => filled($this->accountId))
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
                        reportType: 'general-ledger',
                        parameters: [
                            'accountId' => (int) $this->accountId,
                            'startDate' => $this->startDate,
                            'endDate'   => $this->endDate,
                            'branch_id' => $this->branchId ? (int) $this->branchId : null,
                        ],
                        recipientEmail: $data['email'],
                        formats: $formats,
                    );

                    Notification::make()
                        ->title('Report Generation Queued')
                        ->body("General Ledger report will be emailed to {$data['email']} shortly.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
