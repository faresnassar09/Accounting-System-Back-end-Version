<?php

namespace Modules\Accounting\Jobs;

use App\Models\Tenant;
use App\Services\Logging\LoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Accounting\Exports\BalanceSheetExport;
use Modules\Accounting\Exports\CashFlowExport;
use Modules\Accounting\Exports\GeneralLedgerExport;
use Modules\Accounting\Exports\IncomeStatementExport;
use Modules\Accounting\Exports\TrialBalanceExport;
use Modules\Accounting\Services\Reports\BalanceSheetService;
use Modules\Accounting\Services\Reports\CashFlowStatementService;
use Modules\Accounting\Services\Reports\GeneralLedgerService;
use Modules\Accounting\Services\Reports\IncomeStatementService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Services\Reports\TrialBalanceService;

class GenerateAndSendReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $reportType,
        public array $parameters,
        public string $recipientEmail,
        public array $formats = ['pdf', 'excel'],
        public ?string $tenantId = null
    ) {



    }

    public function handle(
        ReportExportService $reportExportService,
        TrialBalanceService $trialBalanceService,
        GeneralLedgerService $generalLedgerService,
        IncomeStatementService $incomeStatementService,
        BalanceSheetService $balanceSheetService,
        CashFlowStatementService $cashFlowService,
        LoggerService $loggerService
    ): void {

        try {
            switch ($this->reportType) {
                case 'trial-balance':
                    $endDate = $this->parameters['endDate'] ?? now()->format('Y-m-d');
                    $branchId = $this->parameters['branch_id'] ?? $this->parameters['branchId'] ?? null;
                    $branchId = $branchId ? (int) $branchId : null;
                    $reportData = $trialBalanceService->generateReport($endDate, $branchId);
                    $reportTitle = 'Trial Balance';
                    $period = "As of {$endDate}";
                    $filenameBase = 'trial_balance_' . $endDate;
                    $view = 'accounting::reports.pdf.trial-balance';
                    $viewData = ['data' => $reportData, 'endDate' => $endDate];
                    $exportObject = new TrialBalanceExport($reportData, $endDate);
                    break;

                case 'general-ledger':
                    $reportData = $generalLedgerService->generateReport($this->parameters);
                    $startDate = $this->parameters['startDate'] ?? $this->parameters['start_date'] ?? null;
                    $endDate = $this->parameters['endDate'] ?? $this->parameters['end_date'] ?? now()->format('Y-m-d');
                    $accountNumber = $reportData['account_info']['number'] ?? 'account';
                    $accountName = $reportData['account_info']['name'] ?? 'Account';
                    $reportTitle = "General Ledger - {$accountName} ({$accountNumber})";
                    $period = 'Period: ' . ($startDate ?? 'Beginning') . ' to ' . $endDate;
                    $filenameBase = "general_ledger_{$accountNumber}_{$endDate}";
                    $view = 'accounting::reports.pdf.general-ledger';
                    $viewData = ['data' => $reportData, 'startDate' => $startDate, 'endDate' => $endDate];
                    $exportObject = new GeneralLedgerExport($reportData, $startDate, $endDate);
                    break;

                case 'income-statement':
                    $startDate = $this->parameters['startDate'] ?? now()->startOfYear()->format('Y-m-d');
                    $endDate = $this->parameters['endDate'] ?? now()->format('Y-m-d');
                    $branchId = $this->parameters['branch_id'] ?? $this->parameters['branchId'] ?? null;
                    $branchId = $branchId ? (int) $branchId : null;
                    $reportData = $incomeStatementService->generateReport($startDate, $endDate, $branchId);
                    $reportTitle = 'Income Statement';
                    $period = "From {$startDate} to {$endDate}";
                    $filenameBase = 'income_statement_' . $endDate;
                    $view = 'accounting::reports.pdf.income-statement';
                    $viewData = ['data' => $reportData, 'startDate' => $startDate, 'endDate' => $endDate];
                    $exportObject = new IncomeStatementExport($reportData, $startDate, $endDate);
                    break;

                case 'balance-sheet':
                    $endDate = $this->parameters['endDate'] ?? now()->format('Y-m-d');
                    $branchId = $this->parameters['branch_id'] ?? $this->parameters['branchId'] ?? null;
                    $branchId = $branchId ? (int) $branchId : null;
                    $reportData = $balanceSheetService->generateReport($endDate, $branchId);
                    $reportTitle = 'Balance Sheet';
                    $period = "As of {$endDate}";
                    $filenameBase = 'balance_sheet_' . $endDate;
                    $view = 'accounting::reports.pdf.balance-sheet';
                    $viewData = ['data' => $reportData, 'endDate' => $endDate];
                    $exportObject = new BalanceSheetExport($reportData, $endDate);
                    break;

                case 'cash-flow':
                    $startDate = $this->parameters['startDate'] ?? $this->parameters['start_date'] ?? now()->startOfYear()->format('Y-m-d');
                    $endDate = $this->parameters['endDate'] ?? $this->parameters['end_date'] ?? now()->format('Y-m-d');
                    $branchId = $this->parameters['branch_id'] ?? $this->parameters['branchId'] ?? null;
                    $branchId = $branchId ? (int) $branchId : null;
                    $reportData = $cashFlowService->generateReport($startDate, $endDate, $branchId);
                    $reportTitle = 'Statement of Cash Flows';
                    $period = "From {$startDate} to {$endDate}";
                    $filenameBase = 'cash_flow_' . $endDate;
                    $view = 'accounting::reports.pdf.cash-flow';
                    $viewData = ['data' => $reportData, 'startDate' => $startDate, 'endDate' => $endDate];
                    $exportObject = new CashFlowExport($reportData);
                    break;

                default:
                    throw new \InvalidArgumentException("Unsupported report type: {$this->reportType}");
            }

            $reportExportService->sendReportMail(
                recipientEmail: $this->recipientEmail,
                reportTitle: $reportTitle,
                period: $period,
                filenameBase: $filenameBase,
                view: $view,
                viewData: $viewData,
                exportObject: $exportObject,
                formats: $this->formats,
                queue: false // Already executing within a queued worker
            );

            $loggerService->successLogger(
                "{$reportTitle} report generated and emailed successfully via background queue",
                [
                    'recipient'  => $this->recipientEmail,
                    'formats'    => $this->formats,
                    'reportType' => $this->reportType,
                ]
            );
        } catch (\Throwable $e) {
            $loggerService->failedLogger(
                "Failed to generate and email {$this->reportType} report in background queue",
                [
                    'recipient'  => $this->recipientEmail,
                    'formats'    => $this->formats,
                    'reportType' => $this->reportType,
                ],
                $e->getMessage()
            );

            throw $e;
        }
    }
}
