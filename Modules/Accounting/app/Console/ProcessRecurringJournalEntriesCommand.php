<?php

namespace Modules\Accounting\Console;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Accounting\Services\CoreAccounting\RecurringJournalEntryService;

class ProcessRecurringJournalEntriesCommand extends Command
{
    protected $signature = 'accounting:process-recurring
                            {--tenant= : Specific tenant ID to process}
                            {--date= : Custom evaluation date (YYYY-MM-DD)}';

    protected $description = 'Process and post all due recurring journal entries across active schedules';

    public function handle(RecurringJournalEntryService $service): int
    {
        $targetDate = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $this->info("Processing recurring journal entries as of: {$targetDate->toDateString()}");

        $tenantId = $this->option('tenant');

        if (tenancy()->initialized) {
            $this->processTenant($service, $targetDate);
            return Command::SUCCESS;
        }

        $tenantsQuery = Tenant::query();
        if ($tenantId) {
            $tenantsQuery->where('id', $tenantId);
        }

        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found to process.');
            return Command::SUCCESS;
        }

        $totalProcessed = 0;
        $totalSucceeded = 0;
        $totalFailed = 0;

        foreach ($tenants as $tenant) {
            $this->line("<comment>Processing Tenant: {$tenant->id}</comment>");
            tenancy()->initialize($tenant);

            try {
                $results = $service->processDueEntries($targetDate);
                $totalProcessed += $results['processed'];
                $totalSucceeded += $results['succeeded'];
                $totalFailed += $results['failed'];

                if (!empty($results['entries'])) {
                    $this->table(
                        ['Schedule ID', 'Template', 'Entry ID', 'Reference', 'Total ($)', 'Date'],
                        $results['entries']
                    );
                }

                if (!empty($results['errors'])) {
                    foreach ($results['errors'] as $err) {
                        $this->error("  Schedule #{$err['schedule_id']} [{$err['template']}]: {$err['error']}");
                    }
                }

                if ($results['processed'] === 0) {
                    $this->line("  No recurring entries due.");
                }
            } finally {
                tenancy()->end();
            }
        }

        $this->info("Finished: {$totalProcessed} processed, {$totalSucceeded} succeeded, {$totalFailed} failed.");

        return Command::SUCCESS;
    }

    protected function processTenant(RecurringJournalEntryService $service, Carbon $targetDate): void
    {
        $results = $service->processDueEntries($targetDate);
        if (!empty($results['entries'])) {
            $this->table(
                ['Schedule ID', 'Template', 'Entry ID', 'Reference', 'Total ($)', 'Date'],
                $results['entries']
            );
        }
        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $err) {
                $this->error("  Schedule #{$err['schedule_id']} [{$err['template']}]: {$err['error']}");
            }
        }
        $this->info("Execution: {$results['processed']} processed, {$results['succeeded']} succeeded, {$results['failed']} failed.");
    }
}
