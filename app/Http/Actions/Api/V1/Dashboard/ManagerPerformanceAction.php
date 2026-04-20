<?php

namespace App\Http\Actions\Api\V1\Dashboard;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerPerformanceAction
{
    public function __construct(private DashboardService $dashboard) {}

    public function handle(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 25);
        return response()->json(
            $this->dashboard->managerPerformance($request->user()->tenant_id, $perPage),
        );
    }
}
