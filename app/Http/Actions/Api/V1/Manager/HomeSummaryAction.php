<?php

namespace App\Http\Actions\Api\V1\Manager;

use App\Services\Manager\ManagerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeSummaryAction
{
    public function __construct(private ManagerDashboardService $service) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->home((int) $request->user()->tenant_id),
        );
    }
}
