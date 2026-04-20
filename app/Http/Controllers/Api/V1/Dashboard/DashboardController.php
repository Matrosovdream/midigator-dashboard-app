<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Actions\Api\V1\Dashboard\GetDashboardSummaryAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request, GetDashboardSummaryAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
