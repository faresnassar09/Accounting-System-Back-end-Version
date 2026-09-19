<?php

namespace Modules\Teams\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Http\Request;
use Modules\Teams\Models\Team;

class TeamsController extends Controller
{
    public function __construct(public ApiResponseFormatter $apiResponseFormatter) {}

    /**
     * List all active teams for lookup/dropdowns.
     */
    public function index(Request $request)
    {
        $teams = Team::query()
            ->when($request->boolean('active_only', true), fn ($q) => $q->where('active', 1))
            ->orderBy('name')
            ->get(['id', 'name', 'active']);

        return $this->apiResponseFormatter->successResponse(
            'Teams retrieved successfully',
            $teams
        );
    }

    /**
     * Retrieve single team details.
     */
    public function show(int $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return $this->apiResponseFormatter->failedResponse('Team not found', [], 404);
        }

        return $this->apiResponseFormatter->successResponse(
            'Team retrieved successfully',
            $team
        );
    }
}
