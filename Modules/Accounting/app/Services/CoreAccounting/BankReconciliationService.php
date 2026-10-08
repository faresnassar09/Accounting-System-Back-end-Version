<?php

namespace Modules\Accounting\Services\CoreAccounting;

use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Models\BankStatement;
use Modules\Accounting\Models\BankStatementLine;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class BankReconciliationService
{
    public function __construct(
        protected JournalEntryRepositoryInterface $journalRepository,
    ) {}

    /**
     * Import a statement and its line items.
     */
    public function importStatement(
        int $accountId,
        string $statementDate,
        float $openingBalance,
        float $closingBalance,
        array $rows,
        ?string $filename = null
    ): BankStatement {
        return DB::transaction(function () use ($accountId, $statementDate, $openingBalance, $closingBalance, $rows, $filename) {
            $statement = BankStatement::create([
                'account_id'      => $accountId,
                'statement_date'  => $statementDate,
                'filename'        => $filename,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'status'          => 'draft',
            ]);

            foreach ($rows as $row) {
                $statement->lines()->create([
                    'date'        => $row['date'],
                    'description' => $row['description'],
                    'reference'   => $row['reference'] ?? null,
                    'amount'      => (float) $row['amount'],
                    'status'      => 'unmatched',
                ]);
            }

            return $statement;
        });
    }

    /**
     * Automatically match statement lines to unreconciled ledger entries.
     */
    public function autoMatch(BankStatement $statement, int $dateWindowDays = 5): int
    {
        $unmatchedLines = $statement->lines()->where('status', 'unmatched')->get();
        $matchedCount = 0;

        foreach ($unmatchedLines as $sLine) {
            $amount = (float) $sLine->amount;
            $lineDate = Carbon::parse($sLine->date);
            $startDate = $lineDate->copy()->subDays($dateWindowDays)->toDateString();
            $endDate = $lineDate->copy()->addDays($dateWindowDays)->toDateString();

            // Statement positive = deposit (debit in bank account ledger)
            // Statement negative = withdrawal (credit in bank account ledger)
            $isDeposit = ($amount > 0);
            $targetAbsAmount = abs($amount);

            $query = JournalEntryLine::query()
                ->where('account_id', $statement->account_id)
                ->where('is_reconciled', false)
                ->whereBetween('date', [$startDate, $endDate]);

            if ($isDeposit) {
                $query->where('debit', $targetAbsAmount)->where('credit', 0.00);
            } else {
                $query->where('credit', $targetAbsAmount)->where('debit', 0.00);
            }

            $candidate = $query->first();

            if ($candidate) {
                $sLine->update([
                    'matched_journal_entry_line_id' => $candidate->id,
                    'status'                        => 'matched',
                ]);

                $candidate->update([
                    'is_reconciled' => true,
                    'reconciled_at' => now(),
                ]);

                $matchedCount++;
            }
        }

        if ($matchedCount > 0 && $statement->status === 'draft') {
            $statement->update(['status' => 'reconciling']);
        }

        return $matchedCount;
    }

    /**
     * Manually match a specific statement line to a ledger line.
     */
    public function manualMatch(int $statementLineId, int $journalEntryLineId): void
    {
        DB::transaction(function () use ($statementLineId, $journalEntryLineId) {
            $sLine = BankStatementLine::findOrFail($statementLineId);
            $jLine = JournalEntryLine::findOrFail($journalEntryLineId);

            if ($jLine->is_reconciled) {
                throw new DomainException("Journal entry line #{$jLine->id} is already reconciled.");
            }

            $sLine->update([
                'matched_journal_entry_line_id' => $jLine->id,
                'status'                        => 'matched',
            ]);

            $jLine->update([
                'is_reconciled' => true,
                'reconciled_at' => now(),
            ]);

            if ($sLine->statement->status === 'draft') {
                $sLine->statement->update(['status' => 'reconciling']);
            }
        });
    }

    /**
     * Unmatch a statement line and revert the ledger line to unreconciled.
     */
    public function unmatch(int $statementLineId): void
    {
        DB::transaction(function () use ($statementLineId) {
            $sLine = BankStatementLine::findOrFail($statementLineId);

            if ($sLine->matched_journal_entry_line_id) {
                JournalEntryLine::where('id', $sLine->matched_journal_entry_line_id)
                    ->update([
                        'is_reconciled' => false,
                        'reconciled_at' => null,
                    ]);
            }

            $sLine->update([
                'matched_journal_entry_line_id' => null,
                'status'                        => 'unmatched',
            ]);
        });
    }

    /**
     * Create an adjustment entry (e.g., bank fee, interest) directly from an unmatched statement line.
     */
    public function createAdjustmentEntry(
        int $statementLineId,
        int $offsetAccountId,
        string $description,
        ?int $userId = null
    ): JournalEntry {
        return DB::transaction(function () use ($statementLineId, $offsetAccountId, $description, $userId) {
            $sLine = BankStatementLine::with('statement')->findOrFail($statementLineId);
            $amount = (float) $sLine->amount;
            $absAmount = abs($amount);
            $bankAccountId = $sLine->statement->account_id;

            $reference = "ADJ-BNK-" . strtoupper(bin2hex(random_bytes(3)));
            $lines = collect();
            $actorType = ActorType::USER->value;

            if ($amount < 0) {
                // Bank fee / charge: Dr. Expense, Cr. Bank
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $offsetAccountId,
                    'debit'            => $absAmount,
                    'credit'           => 0.00,
                    'description'      => $description,
                ]);
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $bankAccountId,
                    'debit'            => 0.00,
                    'credit'           => $absAmount,
                    'description'      => "Bank deduction: {$description}",
                ]);
            } else {
                // Interest earned: Dr. Bank, Cr. Revenue
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $bankAccountId,
                    'debit'            => $absAmount,
                    'credit'           => 0.00,
                    'description'      => "Bank deposit: {$description}",
                ]);
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $offsetAccountId,
                    'debit'            => 0.00,
                    'credit'           => $absAmount,
                    'description'      => $description,
                ]);
            }

            $header = [
                'reference'     => $reference,
                'status'        => 'approved',
                'total_debit'   => $absAmount,
                'total_credit'  => $absAmount,
                'date'          => Carbon::parse($sLine->date)->format('Y-m-d H:i:s'),
                'description'   => "Reconciliation Adjustment: {$description}",
                'currency_code' => 'USD',
                'exchange_rate' => 1.000000,
            ];

            $journalEntry = $this->journalRepository->store($actorType, $header, $lines, 'adjustment');

            // Find the bank line on this new journal entry and match it immediately
            $bankLine = $journalEntry->lines()->where('account_id', $bankAccountId)->first();
            if ($bankLine) {
                $bankLine->update([
                    'is_reconciled' => true,
                    'reconciled_at' => now(),
                ]);

                $sLine->update([
                    'matched_journal_entry_line_id' => $bankLine->id,
                    'status'                        => 'created_adjustment',
                ]);
            }

            return $journalEntry;
        });
    }

    /**
     * Finalize reconciliation if discrepancy is 0 and no unmatched lines remain.
     */
    public function finalizeReconciliation(BankStatement $statement): bool
    {
        if (abs($statement->discrepancy) > 0.01) {
            throw new DomainException("Cannot finalize: Discrepancy is \${$statement->discrepancy}. Expected 0.00.");
        }

        $statement->update([
            'status'             => 'reconciled',
            'reconciled_balance' => $statement->closing_balance,
        ]);

        return true;
    }
}
