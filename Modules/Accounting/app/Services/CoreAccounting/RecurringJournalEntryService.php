<?php

namespace Modules\Accounting\Services\CoreAccounting;

use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\RecurringJournalEntry;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class RecurringJournalEntryService
{
    public function __construct(
        protected JournalEntryRepositoryInterface $journalRepository,
        protected FinancialClosingReposiroryInterface $financialClosingRepository,
    ) {}

    /**
     * Compute the next execution date based on frequency.
     */
    public function calculateNextRunDate(string $frequency, Carbon $fromDate): Carbon
    {
        $date = $fromDate->copy();

        return match ($frequency) {
            'daily'     => $date->addDay(),
            'weekly'    => $date->addWeek(),
            'monthly'   => $date->addMonthNoOverflow(),
            'quarterly' => $date->addMonthsNoOverflow(3),
            'yearly'    => $date->addYearNoOverflow(),
            default     => $date->addMonthNoOverflow(),
        };
    }

    /**
     * Generate an interpolated, unique journal entry reference.
     */
    public function generateReference(string $template, Carbon $date, int $sequence = 1): string
    {
        $ref = str_replace(
            ['{YYYY}', '{YY}', '{MM}', '{DD}', '{SEQ}'],
            [
                $date->format('Y'),
                $date->format('y'),
                $date->format('m'),
                $date->format('d'),
                str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            ],
            $template
        );

        if ($ref === $template) {
            $ref .= '-' . $date->format('Ym') . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        }

        // Ensure uniqueness if collided
        $candidate = $ref;
        $counter = 1;
        while (JournalEntry::where('reference', $candidate)->exists()) {
            $candidate = $ref . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    /**
     * Post a single recurring journal entry immediately or as of scheduled date.
     */
    public function postEntry(RecurringJournalEntry $recurring, ?Carbon $postingDate = null, ?int $userId = null): JournalEntry
    {
        if ($recurring->lines->isEmpty()) {
            throw new DomainException("Recurring template #{$recurring->id} has no line items.");
        }

        // Double-entry balance check
        $totalDebit = (float) $recurring->lines->sum('debit');
        $totalCredit = (float) $recurring->lines->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001 || $totalDebit <= 0) {
            throw new DomainException("Recurring entry #{$recurring->id} is unbalanced (Debit: {$totalDebit}, Credit: {$totalCredit}).");
        }

        $postDate = $postingDate ?? Carbon::parse($recurring->next_run_date ?? now());
        $fiscalYear = $postDate->format('Y');

        if ($this->financialClosingRepository->isYearClosed($fiscalYear)) {
            throw new DomainException("Cannot post entry into closed financial year ({$fiscalYear}).");
        }

        return DB::transaction(function () use ($recurring, $postDate, $totalDebit, $totalCredit, $userId) {
            $seqCount = JournalEntry::where('recurring_journal_entry_id', $recurring->id)->count() + 1;
            $reference = $this->generateReference($recurring->reference_template, $postDate, $seqCount);

            $status = $recurring->auto_post ? 'approved' : 'draft';

            $header = [
                'reference'                  => $reference,
                'status'                     => $status,
                'total_credit'               => $totalCredit,
                'total_debit'                => $totalDebit,
                'date'                       => $postDate->format('Y-m-d H:i:s'),
                'description'                => $recurring->description . " ({$postDate->format('M Y')})",
                'branch_id'                  => $recurring->branch_id,
                'currency_code'              => $recurring->currency_code ?? 'USD',
                'exchange_rate'              => $recurring->exchange_rate ?? 1.000000,
                'recurring_journal_entry_id' => $recurring->id,
            ];

            $actorType = ActorType::USER->value;
            $lines = $recurring->lines->map(function ($line) use ($userId, $recurring) {
                return [
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $line->account_id,
                    'branch_id'        => $line->branch_id ?? $recurring->branch_id,
                    'debit'            => (float) $line->debit,
                    'credit'           => (float) $line->credit,
                    'description'      => $line->description ?? $recurring->description,
                ];
            });

            $journalEntry = $this->journalRepository->store($actorType, $header, $lines, 'journal');

            // Advance schedule
            $recurring->last_run_date = $postDate->toDateString();
            $nextRun = $this->calculateNextRunDate($recurring->frequency, $postDate);

            if ($recurring->end_date && $nextRun->gt(Carbon::parse($recurring->end_date))) {
                $recurring->status = 'completed';
            } else {
                $recurring->next_run_date = $nextRun->toDateString();
            }

            $recurring->save();

            return $journalEntry;
        });
    }

    /**
     * Batch process all due recurring journal entries across active schedules.
     */
    public function processDueEntries(?Carbon $targetDate = null, ?int $userId = null): array
    {
        $asOf = $targetDate ?? now();
        $dueEntries = RecurringJournalEntry::with(['lines', 'currency', 'branch'])
            ->due($asOf)
            ->get();

        $results = [
            'processed' => 0,
            'succeeded' => 0,
            'failed'    => 0,
            'entries'   => [],
            'errors'    => [],
        ];

        foreach ($dueEntries as $entry) {
            $results['processed']++;
            try {
                $posted = $this->postEntry($entry, Carbon::parse($entry->next_run_date), $userId);
                $results['succeeded']++;
                $results['entries'][] = [
                    'schedule_id' => $entry->id,
                    'template'    => $entry->reference_template,
                    'entry_id'    => $posted->id,
                    'reference'   => $posted->reference,
                    'total'       => (float) $posted->total_debit,
                    'date'        => $posted->date->format('Y-m-d'),
                ];
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'schedule_id' => $entry->id,
                    'template'    => $entry->reference_template,
                    'error'       => $e->getMessage(),
                ];
                Log::error("Failed to post recurring journal entry #{$entry->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Toggle pause / resume status for a recurring entry.
     */
    public function toggleStatus(RecurringJournalEntry $recurring): RecurringJournalEntry
    {
        if ($recurring->status === 'active') {
            $recurring->status = 'paused';
        } elseif ($recurring->status === 'paused') {
            $recurring->status = 'active';
            // If next_run_date is in the past, reset it to today
            if (Carbon::parse($recurring->next_run_date)->isPast()) {
                $recurring->next_run_date = now()->toDateString();
            }
        }

        $recurring->save();

        return $recurring;
    }
}
