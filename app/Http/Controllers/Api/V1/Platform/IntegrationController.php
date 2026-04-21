<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Integration\HealthMatrixAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function health(Request $request, HealthMatrixAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
