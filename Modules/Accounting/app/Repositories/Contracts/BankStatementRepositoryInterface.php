<?php

namespace Modules\Accounting\Repositories\Contracts;

use Modules\Accounting\Models\BankStatement;
use Illuminate\Database\Eloquent\Collection;

interface BankStatementRepositoryInterface
{
    public function getStatements(?int $accountId, ?string $status): Collection;
    public function getStatementById(int $id): BankStatement;
    public function getUnreconciledLedgerLines(int $accountId, ?string $asOfDate): Collection;
}
