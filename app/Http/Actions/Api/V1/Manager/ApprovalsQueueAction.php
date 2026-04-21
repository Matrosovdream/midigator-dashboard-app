<?php

namespace App\Http\Actions\Api\V1\Manager;

use App\Services\Manager\ManagerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalsQueueAction
{
    public function __construct(private ManagerDashboardService $service) {}

    public function handle(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 50);
        return response()->json(
            $this->service->approvals((int) $request->user()->tenant_id, $perPage),
        );
    }
}
