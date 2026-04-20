<?php

namespace App\Http\Controllers\Api\V1\Chargeback;

use App\Http\Actions\Api\V1\Cases\AssignCaseAction;
use App\Http\Actions\Api\V1\Cases\ChangeCaseStageAction;
use App\Http\Actions\Api\V1\Cases\HideCaseAction;
use App\Http\Actions\Api\V1\Cases\ListCasesAction;
use App\Http\Actions\Api\V1\Cases\ShowCaseAction;
use App\Http\Actions\Api\V1\Cases\UpdateCaseAction;
use App\Http\Controllers\Controller;
use App\Services\Cases\CaseRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChargebackController extends Controller
{
    private const TYPE = CaseRegistry::CHARGEBACK;

    public function index(Request $request, ListCasesAction $action): JsonResponse
    {
        return $action->handle($request, self::TYPE);
    }

    public function show(int $id, ShowCaseAction $action): JsonResponse
    {
        return $action->handle(self::TYPE, $id);
    }

    public function update(Request $request, int $id, UpdateCaseAction $action): JsonResponse
    {
        return $action->handle($request, self::TYPE, $id);
    }

    public function changeStage(Request $request, int $id, ChangeCaseStageAction $action): JsonResponse
    {
        return $action->handle($request, self::TYPE, $id);
    }

    public function assign(Request $request, int $id, AssignCaseAction $action): JsonResponse
    {
        return $action->handle($request, self::TYPE, $id);
    }

    public function hide(Request $request, int $id, HideCaseAction $action): JsonResponse
    {
        return $action->handle($request, self::TYPE, $id);
    }

    public function bulkHide(Request $request, HideCaseAction $action): JsonResponse
    {
        return $action->bulk($request, self::TYPE);
    }
}
