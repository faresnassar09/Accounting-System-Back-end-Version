<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\StoreRecurringJournalEntryRequest;
use Modules\Accounting\Http\Requests\UpdateRecurringJournalEntryRequest;
use Modules\Accounting\Services\CoreAccounting\RecurringJournalEntryService;

class RecurringJournalEntryController extends Controller
{
    public function __construct(
        protected RecurringJournalEntryService $recurringService,
        protected ApiResponseFormatter $apiResponseFormatter
    ) {}

    public function index(Request $request)
    {
        try {
            $status = $request->query('status');
            $branchId = $request->query('branch_id');

            $entries = $this->recurringService->getEntries($status, $branchId);

            return $this->apiResponseFormatter->successResponse(
                'Recurring journal entries retrieved successfully',
                $entries
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed retrieving recurring entries: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    public function store(StoreRecurringJournalEntryRequest $request)
    {
        try {
            $data = $request->validated();
            $lines = $data['lines'];
            unset($data['lines']);

            $totalDebit = round(collect($lines)->sum('debit'), 2);
            $totalCredit = round(collect($lines)->sum('credit'), 2);

            $data['total_debit'] = $totalDebit;
            $data['total_credit'] = $totalCredit;
            $data['created_by'] = auth()->id();
            $data['status'] = 'active';

            // Next run date defaults to start_date
            $data['next_run_date'] = $data['start_date'];

            $entry = $this->recurringService->createEntry($data, $lines);

            return $this->apiResponseFormatter->successResponse(
                'Recurring journal entry created successfully',
                $entry->load(['lines.account', 'branch']),
                201
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed creating recurring entry: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function show(int $id)
    {
        try {
            $entry = $this->recurringService->getEntryById($id);

            return $this->apiResponseFormatter->successResponse(
                'Recurring journal entry retrieved successfully',
                $entry
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Recurring entry not found: ' . $e->getMessage(),
                [],
                404
            );
        }
    }

    public function update(int $id, UpdateRecurringJournalEntryRequest $request)
    {
        try {
            $validated = $request->validated();

            $entry = $this->recurringService->updateEntry($id, $validated);

            return $this->apiResponseFormatter->successResponse(
                'Recurring journal entry updated successfully',
                $entry->load(['lines.account', 'branch'])
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed updating recurring entry: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->recurringService->deleteEntry($id);

            return $this->apiResponseFormatter->successResponse(
                'Recurring journal entry deleted successfully',
                null
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed deleting recurring entry: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function run(int $id)
    {
        try {
            $entry = $this->recurringService->getEntryById($id);
            $posted = $this->recurringService->postEntry($entry, null, auth()->id());

            return $this->apiResponseFormatter->successResponse(
                'Recurring entry posted successfully as Journal Entry #' . $posted->id,
                [
                    'journal_entry' => $posted->load('lines.account'),
                    'recurring_entry' => $entry->fresh(['lines.account']),
                ]
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed posting recurring entry: ' . $e->getMessage(),
                [],
                422
            );
        }
    }
}
