<?php

namespace App\Http\Actions\Api\V1\Platform\Integration;

use App\Services\Platform\IntegrationHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthMatrixAction
{
    public function __construct(private IntegrationHealthService $health) {}

    public function handle(Request $request): JsonResponse
    {
        $window = (int) $request->input('window_hours', 24);
        $window = max(1, min($window, 168));
        return response()->json($this->health->matrix($window));
    }
}
