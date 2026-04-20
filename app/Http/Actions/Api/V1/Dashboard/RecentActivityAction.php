<?php

namespace App\Http\Actions\Api\V1\Dashboard;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecentActivityAction
{
    public function __construct(private DashboardService $dashboard) {}

    public function handle(Request $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', 20);
        return response()->json(
            $this->dashboard->recentActivity($request->user()->tenant_id, $limit),
        );
    }
}
