<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Setting\DeleteSettingAction;
use App\Http\Actions\Api\V1\Platform\Setting\ListSettingsAction;
use App\Http\Actions\Api\V1\Platform\Setting\UpsertSettingsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(ListSettingsAction $action): JsonResponse
    {
        return $action->handle();
    }

    public function upsert(Request $request, UpsertSettingsAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function destroy(Request $request, DeleteSettingAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
