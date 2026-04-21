<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Right\ListRightsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RightController extends Controller
{
    public function index(ListRightsAction $action): JsonResponse
    {
        return $action->handle();
    }
}
