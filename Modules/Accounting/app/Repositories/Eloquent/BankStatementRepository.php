<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Models\BankStatement;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Repositories\Contracts\BankStatementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BankStatementRepository implements BankStatementRepositoryInterface
{
    public function getStatements(?int $accountId, ?string $status): Collection
    {
        return BankStatement::with(['account', 'lines'])
            ->when($accountId, fn ($q) => $q->where('account_id', $accountId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('statement_date', 'desc')
            ->get();
    }

    public function getStatementById(int $id): BankStatement
    {
        return BankStatement::with([
            'account',
            'lines.matchedJournalLine.journalEntry',
        ])->findOrFail($id);
    }

    public function getUnreconciledLedgerLines(int $accountId, ?string $asOfDate): Collection
    {
        return JournalEntryLine::with('journalEntry')
            ->where('account_id', $accountId)
            ->where(function ($q) {
                $q->where('is_reconciled', false)->orWhereNull('is_reconciled');
            })
            ->when($asOfDate, fn ($q) => $q->where('date', '<=', $asOfDate))
            ->orderBy('date', 'desc')
            ->get();
    }
}
