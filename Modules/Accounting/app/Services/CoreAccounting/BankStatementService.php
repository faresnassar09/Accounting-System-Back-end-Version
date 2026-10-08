<?php

namespace Modules\Accounting\Services\CoreAccounting;

use Modules\Accounting\Models\BankStatement;
use Modules\Accounting\Models\BankStatementLine;
use Modules\Accounting\Repositories\Contracts\BankStatementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BankStatementService
{
    public function __construct(
        protected BankStatementRepositoryInterface $repository,
        protected BankReconciliationService $reconciliationService
    ) {}

    public function getStatements(?int $accountId, ?string $status): Collection
    {
        return $this->repository->getStatements($accountId, $status);
    }

    public function getStatementById(int $id): BankStatement
    {
        return $this->repository->getStatementById($id);
    }

    public function getUnreconciledLedgerLines(int $accountId, ?string $asOfDate): Collection
    {
        return $this->repository->getUnreconciledLedgerLines($accountId, $asOfDate);
    }

    public function importStatement(array $data): BankStatement
    {
        return $this->reconciliationService->importStatement(
            $data['account_id'],
            $data['statement_date'],
            (float) $data['opening_balance'],
            (float) $data['closing_balance'],
            $data['rows'],
            $data['filename'] ?? null
        );
    }

    public function autoMatch(int $id, int $windowDays): array
    {
        $statement = $this->getStatementById($id);
        $matchedCount = $this->reconciliationService->autoMatch($statement, $windowDays);
        return [
            'matched_count' => $matchedCount,
            'statement'     => $statement->fresh(['lines.matchedJournalLine.journalEntry'])
        ];
    }

    public function matchLine(int $statementLineId, int $journalEntryLineId): BankStatementLine
    {
        $this->reconciliationService->manualMatch($statementLineId, $journalEntryLineId);
        return BankStatementLine::findOrFail($statementLineId)->fresh(['matchedJournalLine.journalEntry']);
    }

    public function unmatchLine(int $statementLineId): BankStatementLine
    {
        $this->reconciliationService->unmatch($statementLineId);
        return BankStatementLine::findOrFail($statementLineId)->fresh();
    }

    public function reconcile(int $id): BankStatement
    {
        $statement = $this->getStatementById($id);
        $this->reconciliationService->finalizeReconciliation($statement);
        return $statement->fresh(['account', 'lines']);
    }
}
