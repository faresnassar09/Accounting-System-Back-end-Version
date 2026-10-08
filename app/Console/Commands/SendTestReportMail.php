<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Modules\Accounting\Exports\TrialBalanceExport;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Services\Reports\TrialBalanceService;

class SendTestReportMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:send-test-report 
                            {email=fares.ahmed.nassar0@gmail.com : The recipient email address}
                            {--date=2026-12-31 : The report end date}
                            {--tenant= : Optional tenant ID to initialize}
                            {--attach=pdf,excel : Formats to attach: pdf, excel, or pdf,excel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Trial Balance PDF and Excel reports and email user-selected attachments';

    /**
     * Execute the console command.
     */
    public function handle(TrialBalanceService $trialBalanceService, ReportExportService $reportExportService)
    {
        $email = $this->argument('email');
        $date = $this->option('date') ?: '2026-12-31';
        $tenantId = $this->option('tenant');
        $rawAttach = $this->option('attach') ?: 'pdf,excel';
        $formats = array_map('trim', explode(',', strtolower($rawAttach)));

        $reportData = null;

        try {
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if (! $tenant) {
                    $this->error("Tenant '{$tenantId}' not found.");
                    return 1;
                }
                tenancy()->initialize($tenant);
            } else {
                $tenant = Tenant::first();
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
            }

            $this->info("Fetching Trial Balance from database for date: {$date}...");
            $reportData = $trialBalanceService->generateReport($date);
        } catch (\Throwable $e) {
            $this->warn("Database query skipped ({$e->getMessage()}). Using verified sample Trial Balance dataset for generation test.");
            $reportData = [
                'endDate'    => $date,
                'reportData' => [
                    (object)['id' => 1, 'number' => '1010', 'name' => 'Cash on Hand', 'period_debit' => 15000.00, 'period_credit' => 0.00, 'final_debit_balance' => 15000.00, 'final_credit_balance' => 0.00],
                    (object)['id' => 2, 'number' => '1020', 'name' => 'Bank Account', 'period_debit' => 35000.00, 'period_credit' => 5000.00, 'final_debit_balance' => 30000.00, 'final_credit_balance' => 0.00],
                    (object)['id' => 3, 'number' => '2010', 'name' => 'Accounts Payable', 'period_debit' => 2000.00, 'period_credit' => 12000.00, 'final_debit_balance' => 0.00, 'final_credit_balance' => 10000.00],
                    (object)['id' => 4, 'number' => '3010', 'name' => 'Capital / Equity', 'period_debit' => 0.00, 'period_credit' => 35000.00, 'final_debit_balance' => 0.00, 'final_credit_balance' => 35000.00],
                ],
                'totals' => [
                    'total_debit'  => 45000.00,
                    'total_credit' => 45000.00,
                    'isBalanced'   => true,
                ],
            ];
        }

        $formatLabel = strtoupper(implode(' & ', $formats));
        $this->info("Compiling [{$formatLabel}] and sending email to: {$email}...");

        $reportExportService->sendReportMail(
            recipientEmail: $email,
            reportTitle: 'Trial Balance',
            period: "As of {$date}",
            filenameBase: 'trial_balance_' . $date,
            view: 'accounting::reports.pdf.trial-balance',
            viewData: ['data' => $reportData, 'endDate' => $date],
            exportObject: new TrialBalanceExport($reportData, $date),
            formats: $formats
        );

        $this->info("SUCCESS: Email containing Trial Balance [{$formatLabel}] was dispatched to {$email}!");
        $mailer = config('mail.default');
        $this->comment("Mail driver is set to: [{$mailer}]. " . ($mailer === 'log' ? "Email content with attachments was logged to storage/logs/laravel.log" : "Sent via {$mailer}."));

        return 0;
    }
}
