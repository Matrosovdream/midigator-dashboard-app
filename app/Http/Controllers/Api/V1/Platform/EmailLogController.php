<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\EmailLog\ListEmailLogsAction;
use App\Http\Actions\Api\V1\Platform\EmailLog\ShowEmailLogAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request, ListEmailLogsAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function show(int $id, ShowEmailLogAction $action): JsonResponse
    {
        return $action->handle($id);
    }
}
