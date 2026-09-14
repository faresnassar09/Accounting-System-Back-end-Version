<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Queries\FinancialClosingQuery;
use Modules\Accounting\Queries\GetProfitAndLossTotalsQuery;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class FinancialClosingService
{
    public function __construct(
        public FinancialClosingReposiroryInterface $financialClosingInterFace,
        public GetProfitAndLossTotalsQuery $getProfitAndLossTotalsQuery,
        public FinancialClosingQuery $financialClosingQuery,
        public JournalEntryRepositoryInterface $journalRepositoryInterface,
        public LoggerService $loggerService,
    ) {}

    public function getRevenuesAndExpenses($year): array
    {
        $startDate = get_start_of_year($year);
        $endDate   = get_end_of_year($year);
        $summary   = ($this->getProfitAndLossTotalsQuery)($startDate, $endDate);

        return [
            'total_revenues' => (float) ($summary->total_revenues ?? 0.00),
            'total_expenses' => (float) ($summary->total_expenses ?? 0.00),
            'net_profit'     => (float) ($summary->net_profit ?? 0.00),
        ];
    }

    public function applyClosingFinancialYear($data): bool
    {
        $accountId = is_array($data) ? $data['account_id'] : ($data->account_id ?? $data['account_id']);
        $year      = is_array($data) ? $data['year'] : ($data->year ?? $data['year']);
        $startFrom = get_start_of_year($year);
        $endAt     = get_end_of_year($year);
        $actorType = ActorType::USER->value;
        $userId    = current_guard_user()?->id ?? 1;

        DB::transaction(function () use (
            $actorType,
            $userId,
            $year,
            $endAt,
            $startFrom,
            $accountId,
        ) {
            $alreadyClosed = $this->financialClosingInterFace->isYearClosed($year);

            if ($alreadyClosed) {
                throw new \Exception("Financial Year ( $year ) Already Closed");
            }

            // 1. Generate P&L closing lines and exact totals directly from SQL (Zero PHP math)
            $closingData = $this->financialClosingQuery->getClosingPnlData($startFrom, $endAt, $userId);

            $header = [
                'date'         => now(),
                'reference'    => (string) Str::uuid(),
                'description'  => "closing journal for year ($year)",
                'total_debit'  => $closingData['total_amount'],
                'total_credit' => $closingData['total_amount'],
            ];

            $journalHeader = $this->journalRepositoryInterface->store(
                $actorType,
                $header,
                $closingData['lines'],
                'closing'
            );

            if ($closingData['diff_totals'] != 0) {
                $this->journalRepositoryInterface->storeDiffBalancerLines(
                    $actorType,
                    $userId,
                    $journalHeader,
                    $closingData['diff_totals'],
                    $accountId,
                );
            }

            $this->financialClosingInterFace->flagYearAsClosed(
                $year,
                $closingData['diff_totals'],
                $accountId
            );

            // 2. Generate Balance Sheet opening lines for next year directly from SQL (Zero PHP math)
            $openingData = $this->financialClosingQuery->getNextYearOpeningData($endAt, $userId);
            $nextYear    = get_start_of_next_financial_year($year);

            $openingHeader = [
                'date'         => "$nextYear-1-1",
                'reference'    => (string) Str::uuid(),
                'description'  => "Opening journal for year ($nextYear)",
                'total_debit'  => $openingData['total_amount'],
                'total_credit' => $openingData['total_amount'],
            ];

            $this->journalRepositoryInterface->store(
                $actorType,
                $openingHeader,
                $openingData['lines'],
                'opening'
            );
        });

        return true;
    }
}
