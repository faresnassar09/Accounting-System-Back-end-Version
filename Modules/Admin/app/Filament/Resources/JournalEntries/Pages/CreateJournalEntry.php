<?php

namespace Modules\Admin\Filament\Resources\JournalEntries\Pages;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Admin\Filament\Resources\JournalEntries\JournalEntryResource;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $lines = $data['lines'] ?? [];

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'data.lines' => 'A journal entry must contain at least two line items.',
            ]);
        }

        $totalDebit = round(collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0)), 2);
        $totalCredit = round(collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0)), 2);

        if ($totalDebit <= 0.00) {
            throw ValidationException::withMessages([
                'data.lines' => 'Total Debits and Credits must be greater than zero.',
            ]);
        }

        if ($totalDebit !== $totalCredit) {
            $diff = abs($totalDebit - $totalCredit);
            throw ValidationException::withMessages([
                'data.lines' => "Journal Entry is out of balance! Total Debits (\${$totalDebit}) must equal Total Credits (\${$totalCredit}). Difference: \${$diff}",
            ]);
        }

        $postingYear = Carbon::parse($data['date'] ?? now())->format('Y');
        $financialClosingRepo = app(FinancialClosingReposiroryInterface::class);

        if ($financialClosingRepo->isYearClosed($postingYear)) {
            throw ValidationException::withMessages([
                'data.date' => "Cannot post journal entry into a closed financial year ({$postingYear}).",
            ]);
        }

        return DB::transaction(function () use ($data, $lines, $totalDebit, $totalCredit) {
            $entry = JournalEntry::create([
                'reference'    => $data['reference'],
                'date'         => $data['date'] ?? now(),
                'description'  => $data['description'],
                'branch_id'    => $data['branch_id'] ?? null,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'status'       => 'approved',
                'type'         => 'journal',
            ]);

            $userId = current_guard_user()?->id ?? 1;

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'source_type'      => 'admin',
                    'source_reference' => $userId,
                    'account_id'       => $line['account_id'],
                    'branch_id'        => $line['branch_id'] ?? $entry->branch_id ?? null,
                    'debit'            => (float) ($line['debit'] ?? 0),
                    'credit'           => (float) ($line['credit'] ?? 0),
                    'date'             => $entry->date,
                ]);
            }

            return $entry;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
