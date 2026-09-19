<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;

use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\JournalEntryRequest;
use Modules\Accounting\Http\Requests\ReverseJournalEntryRequest;
use Modules\Accounting\Jobs\CreateJournalEntryJob;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;
use Modules\Accounting\Transformers\JournalEntryListResource;
use Modules\Accounting\Transformers\JournalEntryResource;

class JournalEntriesController extends Controller
{

    public function __construct(

        public JournalEntryService $journalEntryService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,

    ) {}

    /**
     * 
     * list journal entries with filters & pagination
     *
     * @group journal entry
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', $request->input('perPage', 15));
            $entries = $this->journalEntryService->getJournalEntries($request->all(), $perPage);

            return $this->apiResponseFormatter->successResponse(
                'Journal entries retrieved successfully',
                JournalEntryListResource::collection($entries)->response()->getData(true)
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'Error Occurred While Retrieving Journal Entries',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Error Occurred While Retrieving Journal Entries',
                [],
                500
            );
        }
    }

    /**
     * 
     * view a single journal entry with its lines
     *
     * @group journal entry
     */
    public function show(int $id)
    {
        try {
            $entry = $this->journalEntryService->getJournalEntry($id);

            return $this->apiResponseFormatter->successResponse(
                'Journal entry retrieved successfully',
                new JournalEntryResource($entry)
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseFormatter->failedResponse(
                $e->getMessage(),
                [],
                404
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'Error Occurred While Retrieving Journal Entry',
                ['id' => $id],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Error Occurred While Retrieving Journal Entry',
                [],
                500
            );
        }
    }

    /**
     * 
     * store normal journal entry 
     *
     * @group journal entry
     */
    public function store(JournalEntryRequest $data)
    {

        try {

            $userId = current_guard_user()?->id;
            CreateJournalEntryJob::dispatch($data->validated(), $userId);

            return $this->apiResponseFormatter->successResponse(

                'Journal Entry Saved Successfully',
                [],
                201

            );
            
        } catch (\Exception $e) {

            $this->loggerService->failedLogger(
                'Error Occurred While Saving Journal Entry',
                [],
                $e->getMessage()

            );

            return $this->apiResponseFormatter->failedResponse(

                'Error Occurred While Saving Journal Entry',
                [],

            );
        }
    }

    /**
     * 
     * reverse / void a posted journal entry
     *
     * @group journal entry
     */
    public function reverse(int $id, ReverseJournalEntryRequest $request)
    {
        try {
            $userId = current_guard_user()?->id;
            $reversingEntry = $this->journalEntryService->reverse(
                id: $id,
                reason: $request->input('reason'),
                reversalDate: $request->input('reversal_date'),
                userId: $userId
            );

            return $this->apiResponseFormatter->successResponse(
                'Journal entry reversed successfully',
                new JournalEntryResource($reversingEntry),
                201
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseFormatter->failedResponse(
                $e->getMessage(),
                [],
                404
            );
        } catch (\DomainException $e) {
            return $this->apiResponseFormatter->failedResponse(
                $e->getMessage(),
                [],
                422
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'Error Occurred While Reversing Journal Entry',
                ['id' => $id],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Error Occurred While Reversing Journal Entry',
                [],
                500
            );
        }
    }
}
