<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\StoreFixedAssetRequest;
use Modules\Accounting\Http\Requests\UpdateFixedAssetRequest;
use Modules\Accounting\Http\Requests\PostDepreciationRequest;
use Modules\Accounting\Http\Requests\DisposeAssetRequest;
use Modules\Accounting\Services\CoreAccounting\FixedAssetService;

class FixedAssetController extends Controller
{
    public function __construct(
        protected FixedAssetService $fixedAssetService,
        protected ApiResponseFormatter $apiResponseFormatter
    ) {}

    public function index(Request $request)
    {
        try {
            $status = $request->query('status');

            $assets = $this->fixedAssetService->getAssets($status);

            return $this->apiResponseFormatter->successResponse(
                'Fixed assets retrieved successfully',
                $assets
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed retrieving fixed assets: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    public function store(StoreFixedAssetRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Auto-generate asset number if missing
            if (empty($data['asset_number'])) {
                $data['asset_number'] = 'FA-' . strtoupper(uniqid());
            }

            // Initialization defaults
            $data['book_value'] = $data['purchase_cost'];
            $data['accumulated_depreciation'] = 0.00;
            $data['status'] = 'active';

            $asset = $this->fixedAssetService->createAsset($data);

            return $this->apiResponseFormatter->successResponse(
                'Fixed asset registered successfully',
                $asset,
                201
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed registering fixed asset: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function show(int $id)
    {
        try {
            $asset = $this->fixedAssetService->getAssetById($id);

            return $this->apiResponseFormatter->successResponse(
                'Fixed asset retrieved successfully',
                $asset
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Fixed asset not found: ' . $e->getMessage(),
                [],
                404
            );
        }
    }

    public function update(int $id, UpdateFixedAssetRequest $request)
    {
        try {
            $validated = $request->validated();
            $asset = $this->fixedAssetService->updateAsset($id, $validated);

            return $this->apiResponseFormatter->successResponse(
                'Fixed asset updated successfully',
                $asset
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed updating fixed asset: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->fixedAssetService->deleteAsset($id);

            return $this->apiResponseFormatter->successResponse(
                'Fixed asset deleted successfully',
                null
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed deleting fixed asset: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function depreciate(int $id, PostDepreciationRequest $request)
    {
        try {
            $validated = $request->validated();

            $date = isset($validated['period_date']) && $validated['period_date'] ? Carbon::parse($validated['period_date']) : now();
            $asset = $this->fixedAssetService->getAssetById($id);

            $posted = $this->fixedAssetService->postMonthlyDepreciation($asset, $date, auth()->id());

            return $this->apiResponseFormatter->successResponse(
                'Depreciation posted successfully as Journal Entry #' . $posted->id,
                [
                    'journal_entry' => $posted->load('lines.account'),
                    'asset'         => $asset->fresh(),
                ]
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed posting depreciation: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function dispose(int $id, DisposeAssetRequest $request)
    {
        try {
            $validated = $request->validated();

            $date = isset($validated['disposal_date']) && $validated['disposal_date'] ? Carbon::parse($validated['disposal_date']) : now();
            $asset = $this->fixedAssetService->getAssetById($id);

            $posted = $this->fixedAssetService->disposeAsset(
                $asset,
                (float) $validated['disposal_amount'],
                $date,
                $validated['cash_account_id'],
                $validated['gain_loss_account_id'],
                auth()->id()
            );

            return $this->apiResponseFormatter->successResponse(
                'Asset disposed successfully via Journal Entry #' . $posted->id,
                [
                    'journal_entry' => $posted->load('lines.account'),
                    'asset'         => $asset->fresh(),
                ]
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed disposing asset: ' . $e->getMessage(),
                [],
                422
            );
        }
    }
}
