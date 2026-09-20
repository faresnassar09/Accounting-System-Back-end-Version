<?php

namespace Modules\Accounting\Services\CoreAccounting;

use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Models\DepreciationSchedule;
use Modules\Accounting\Models\FixedAsset;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class FixedAssetService
{
    public function __construct(
        protected JournalEntryRepositoryInterface $journalRepository,
        protected FinancialClosingReposiroryInterface $financialClosingRepository,
    ) {}

    /**
     * Calculate monthly depreciation based on configured method and remaining depreciable base.
     */
    public function calculateMonthlyDepreciation(FixedAsset $asset): float
    {
        $depreciableBase = (float) $asset->purchase_cost - (float) $asset->salvage_value;
        $currentAccum = (float) $asset->accumulated_depreciation;
        $remainingDepreciable = max(0.0, $depreciableBase - $currentAccum);

        if ($remainingDepreciable <= 0.0 || $asset->useful_life_months <= 0) {
            return 0.0;
        }

        if ($asset->depreciation_method === 'declining_balance') {
            $annualRate = 2.0 / ($asset->useful_life_months / 12.0);
            $monthlyRate = $annualRate / 12.0;
            $monthly = round(((float) $asset->book_value - (float) $asset->salvage_value) * $monthlyRate, 2);
        } else {
            // Default Straight-Line
            $monthly = round($depreciableBase / $asset->useful_life_months, 2);
        }

        return min($monthly, $remainingDepreciable);
    }

    /**
     * Post monthly depreciation journal entry for a given period date.
     */
    public function postMonthlyDepreciation(FixedAsset $asset, Carbon $periodDate, ?int $userId = null): JournalEntry
    {
        if ($asset->status !== 'active') {
            throw new DomainException("Asset #{$asset->asset_number} cannot be depreciated because it is {$asset->status}.");
        }

        $periodStr = $periodDate->format('Y-m-01');

        $exists = DepreciationSchedule::where('fixed_asset_id', $asset->id)
            ->whereDate('period_date', $periodStr)
            ->exists();

        if ($exists) {
            throw new DomainException("Depreciation for asset #{$asset->asset_number} has already been posted for {$periodDate->format('M Y')}.");
        }

        $fiscalYear = $periodDate->format('Y');
        if ($this->financialClosingRepository->isYearClosed($fiscalYear)) {
            throw new DomainException("Cannot post depreciation into closed financial year ({$fiscalYear}).");
        }

        $depreciationAmount = $this->calculateMonthlyDepreciation($asset);

        if ($depreciationAmount <= 0.0) {
            $asset->update(['status' => 'fully_depreciated']);
            throw new DomainException("Asset #{$asset->asset_number} is fully depreciated.");
        }

        return DB::transaction(function () use ($asset, $depreciationAmount, $periodDate, $periodStr, $userId) {
            $reference = "DEP-{$asset->asset_number}-" . $periodDate->format('Ym');
            $counter = 1;
            while (JournalEntry::where('reference', $reference)->exists()) {
                $reference = "DEP-{$asset->asset_number}-" . $periodDate->format('Ym') . "-{$counter}";
                $counter++;
            }

            $header = [
                'reference'     => $reference,
                'status'        => 'approved',
                'total_debit'   => $depreciationAmount,
                'total_credit'  => $depreciationAmount,
                'date'          => $periodDate->format('Y-m-d H:i:s'),
                'description'   => "Depreciation - {$asset->name} ({$asset->asset_number}) for {$periodDate->format('M Y')}",
                'branch_id'     => $asset->branch_id,
                'currency_code' => 'USD',
                'exchange_rate' => 1.000000,
            ];

            $actorType = ActorType::USER->value;
            $lines = collect([
                [
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $asset->depreciation_expense_account_id,
                    'branch_id'        => $asset->branch_id,
                    'debit'            => $depreciationAmount,
                    'credit'           => 0.00,
                    'description'      => "Depreciation expense: {$asset->name}",
                ],
                [
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $asset->accumulated_depreciation_account_id,
                    'branch_id'        => $asset->branch_id,
                    'debit'            => 0.00,
                    'credit'           => $depreciationAmount,
                    'description'      => "Accumulated depreciation: {$asset->name}",
                ],
            ]);

            $journalEntry = $this->journalRepository->store($actorType, $header, $lines, 'adjustment');

            $newAccum = (float) $asset->accumulated_depreciation + $depreciationAmount;
            $newBookValue = (float) $asset->purchase_cost - $newAccum;
            $isCompleted = ($newBookValue <= (float) $asset->salvage_value);

            $asset->update([
                'accumulated_depreciation' => $newAccum,
                'book_value'               => $newBookValue,
                'status'                   => $isCompleted ? 'fully_depreciated' : 'active',
            ]);

            DepreciationSchedule::create([
                'fixed_asset_id'      => $asset->id,
                'period_date'         => $periodStr,
                'depreciation_amount' => $depreciationAmount,
                'accumulated_after'   => $newAccum,
                'book_value_after'    => $newBookValue,
                'journal_entry_id'    => $journalEntry->id,
            ]);

            return $journalEntry;
        });
    }

    /**
     * Dispose or retire an asset, posting a balanced entry recognizing proceeds, asset derecognition, and gain/loss.
     */
    public function disposeAsset(
        FixedAsset $asset,
        float $disposalAmount,
        Carbon $disposalDate,
        int $cashAccountId,
        int $gainLossAccountId,
        ?int $userId = null
    ): JournalEntry {
        if ($asset->status === 'disposed') {
            throw new DomainException("Asset #{$asset->asset_number} is already disposed.");
        }

        $fiscalYear = $disposalDate->format('Y');
        if ($this->financialClosingRepository->isYearClosed($fiscalYear)) {
            throw new DomainException("Cannot record asset disposal into closed financial year ({$fiscalYear}).");
        }

        return DB::transaction(function () use ($asset, $disposalAmount, $disposalDate, $cashAccountId, $gainLossAccountId, $userId) {
            $cost = (float) $asset->purchase_cost;
            $accum = (float) $asset->accumulated_depreciation;
            $netBookValue = (float) $asset->book_value;
            $gainLoss = round($disposalAmount - $netBookValue, 2);

            $lines = collect();
            $actorType = ActorType::USER->value;

            // 1. Debit Cash / Bank if disposal proceeds received
            if ($disposalAmount > 0.0) {
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $cashAccountId,
                    'branch_id'        => $asset->branch_id,
                    'debit'            => $disposalAmount,
                    'credit'           => 0.00,
                    'description'      => "Disposal proceeds: {$asset->name}",
                ]);
            }

            // 2. Debit Accumulated Depreciation to derecognize prior depreciation
            if ($accum > 0.0) {
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $asset->accumulated_depreciation_account_id,
                    'branch_id'        => $asset->branch_id,
                    'debit'            => $accum,
                    'credit'           => 0.00,
                    'description'      => "Reverse accumulated depreciation on disposal: {$asset->name}",
                ]);
            }

            // 3. Loss on Disposal (Debit) or Gain on Disposal (Credit)
            if ($gainLoss < 0.0) {
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $gainLossAccountId,
                    'branch_id'        => $asset->branch_id,
                    'debit'            => abs($gainLoss),
                    'credit'           => 0.00,
                    'description'      => "Loss on disposal of asset: {$asset->name}",
                ]);
            } elseif ($gainLoss > 0.0) {
                $lines->push([
                    'source_reference' => $userId ?? 0,
                    'account_id'       => $gainLossAccountId,
                    'branch_id'        => $asset->branch_id,
                    'debit'            => 0.00,
                    'credit'           => $gainLoss,
                    'description'      => "Gain on disposal of asset: {$asset->name}",
                ]);
            }

            // 4. Credit Asset Cost Account to remove original asset cost
            $lines->push([
                'source_reference' => $userId ?? 0,
                'account_id'       => $asset->asset_account_id,
                'branch_id'        => $asset->branch_id,
                'debit'            => 0.00,
                'credit'           => $cost,
                'description'      => "Derecognize asset cost: {$asset->name}",
            ]);

            $totalDebit = (float) $lines->sum('debit');
            $totalCredit = (float) $lines->sum('credit');

            $reference = "DISP-{$asset->asset_number}-" . $disposalDate->format('Ymd');
            $header = [
                'reference'     => $reference,
                'status'        => 'approved',
                'total_debit'   => $totalDebit,
                'total_credit'  => $totalCredit,
                'date'          => $disposalDate->format('Y-m-d H:i:s'),
                'description'   => "Asset Disposal: {$asset->name} ({$asset->asset_number})",
                'branch_id'     => $asset->branch_id,
                'currency_code' => 'USD',
                'exchange_rate' => 1.000000,
            ];

            $journalEntry = $this->journalRepository->store($actorType, $header, $lines, 'adjustment');

            $asset->update([
                'status'          => 'disposed',
                'disposal_date'   => $disposalDate->toDateString(),
                'disposal_amount' => $disposalAmount,
                'book_value'      => 0.00,
            ]);

            return $journalEntry;
        });
    }

    /**
     * Batch process depreciation for all active assets for a given period.
     */
    public function postAllDueDepreciations(Carbon $periodDate, ?int $userId = null): array
    {
        $assets = FixedAsset::active()->get();
        $results = ['posted' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($assets as $asset) {
            try {
                $this->postMonthlyDepreciation($asset, $periodDate, $userId);
                $results['posted']++;
            } catch (\DomainException $e) {
                $results['skipped']++;
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'asset' => $asset->asset_number,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
