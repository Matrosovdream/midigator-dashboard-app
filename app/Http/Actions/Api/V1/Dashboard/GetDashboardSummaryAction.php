<?php

namespace App\Http\Actions\Api\V1\Dashboard;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetDashboardSummaryAction
{
    public function __construct(private DashboardService $dashboard) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json(
            $this->dashboard->summaryForTenant($request->user()->tenant_id),
        );
    }
}
