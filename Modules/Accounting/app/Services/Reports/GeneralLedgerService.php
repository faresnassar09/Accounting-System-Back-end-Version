<?php

namespace Modules\Accounting\Services\Reports;

use Modules\Accounting\Queries\GeneralLedgerQuery;
use Modules\Accounting\Queries\GetOpeningBalanceQuery;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as AccountInterface;

class GeneralLedgerService
{
    public function __construct(
        public AccountInterface $AccountInterface,
        public GetOpeningBalanceQuery $getOpeningBalance,
        public GeneralLedgerQuery $generalLedgerQuery,

    ) {}

    public function generateReport(array $data): array
    {
        $account = $this->AccountInterface->findAccount($data['accountId']);
        $accountId = $account->id;
        $endDate   = $data['endDate'] ?? $data['end_date'] ?? now()->format('Y-m-d');
        $startDate = $data['startDate'] ?? $data['start_date'] ?? get_start_of_year($endDate);
        $branchId  = $data['branch_id'] ?? $data['branchId'] ?? null;
        $branchId  = $branchId ? (int) $branchId : null;

        $openingBalance = ($this->getOpeningBalance)([$accountId], $startDate, $branchId);

        $reportData = ($this->generalLedgerQuery)($openingBalance, $accountId, $startDate, $endDate, $branchId);

        return [
            'account_info' => [
                'name' => $account->name,
                'number' => $account->number
            ],
            'opening_balance' => $reportData['opening_balance'],
            'closing_balance' => $reportData['closing_balance'],
            'total_debit'     => $reportData['total_debit'],
            'total_credit'    => $reportData['total_credit'],
            'transactions'    => $reportData['transactions'],
        ];
    }
}
