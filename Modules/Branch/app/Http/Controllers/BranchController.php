<?php

namespace Modules\Branch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Http\Request;
use Modules\Branch\Models\Branch;

class BranchController extends Controller
{
    public function __construct(public ApiResponseFormatter $apiResponseFormatter) {}

    /**
     * List all active branches for lookup/dropdowns.
     */
    public function index(Request $request)
    {
        $branches = Branch::query()
            ->when($request->boolean('active_only', true), fn ($q) => $q->where('active', 1))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'phone', 'address', 'active']);

        return $this->apiResponseFormatter->successResponse(
            'Branches retrieved successfully',
            $branches
        );
    }

    /**
     * Retrieve single branch details.
     */
    public function show(int $id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return $this->apiResponseFormatter->failedResponse('Branch not found', [], 404);
        }

        return $this->apiResponseFormatter->successResponse(
            'Branch retrieved successfully',
            $branch
        );
    }
}
