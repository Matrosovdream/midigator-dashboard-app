<?php

namespace App\Http\Controllers\Api\V1\ActivityLog;

use App\Http\Actions\Api\V1\ActivityLog\ListActivityLogAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request, ListActivityLogAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
