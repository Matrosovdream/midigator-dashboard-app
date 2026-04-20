<?php

namespace App\Http\Actions\Api\V1\ActivityLog;

use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListActivityLogAction
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function handle(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->integer('per_page', 50);
        $filter = (array) $request->input('filter', []);

        if (!$user->hasRight('activity_log.view_all') && !$user->isPlatformAdmin()) {
            $filter['user_id'] = $user->id;
        }

        return response()->json(
            $this->activityLog->listForTenant($user->tenant_id, $perPage, $filter),
        );
    }
}
