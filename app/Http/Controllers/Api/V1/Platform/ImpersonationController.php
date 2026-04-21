<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Impersonation\StartImpersonationAction;
use App\Http\Actions\Api\V1\Platform\Impersonation\StopImpersonationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function start(Request $request, int $userId, StartImpersonationAction $action): JsonResponse
    {
        return $action->handle($request, $userId);
    }

    public function stop(Request $request, StopImpersonationAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
