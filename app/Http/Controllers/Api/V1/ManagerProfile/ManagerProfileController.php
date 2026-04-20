<?php

namespace App\Http\Controllers\Api\V1\ManagerProfile;

use App\Http\Actions\Api\V1\ManagerProfile\UpdateManagerScoreAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerProfileController extends Controller
{
    public function updateScore(Request $request, int $userId, UpdateManagerScoreAction $action): JsonResponse
    {
        return $action->handle($request, $userId);
    }
}
