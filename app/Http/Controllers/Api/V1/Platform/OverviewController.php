<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Overview\SummaryAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OverviewController extends Controller
{
    public function summary(SummaryAction $action): JsonResponse
    {
        return $action->handle();
    }
}
