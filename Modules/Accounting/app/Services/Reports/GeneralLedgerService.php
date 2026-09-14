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
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $openingBalance = ($this->getOpeningBalance)([$accountId], $startDate);

        $reportData = ($this->generalLedgerQuery)($openingBalance, $accountId, $startDate, $endDate);

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
