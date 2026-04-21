<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Actions\Api\V1\Manager\ActivityFeedAction;
use App\Http\Actions\Api\V1\Manager\ApprovalsQueueAction;
use App\Http\Actions\Api\V1\Manager\AssignCaseAction;
use App\Http\Actions\Api\V1\Manager\AssignmentsQueueAction;
use App\Http\Actions\Api\V1\Manager\HomeSummaryAction;
use App\Http\Actions\Api\V1\Manager\TeamOverviewAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerDashboardController extends Controller
{
    public function home(Request $request, HomeSummaryAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function team(Request $request, TeamOverviewAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function assignments(Request $request, AssignmentsQueueAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function assign(Request $request, string $kind, int $id, AssignCaseAction $action): JsonResponse
    {
        return $action->handle($request, $kind, $id);
    }

    public function approvals(Request $request, ApprovalsQueueAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function activity(Request $request, ActivityFeedAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
