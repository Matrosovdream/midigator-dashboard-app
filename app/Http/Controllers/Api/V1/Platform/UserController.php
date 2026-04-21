<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\User\ListPlatformUsersAction;
use App\Http\Actions\Api\V1\Platform\User\ToggleActiveAction;
use App\Http\Actions\Api\V1\Platform\User\TogglePlatformAdminAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, ListPlatformUsersAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function toggleActive(Request $request, int $id, ToggleActiveAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function togglePlatformAdmin(Request $request, int $id, TogglePlatformAdminAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }
}
