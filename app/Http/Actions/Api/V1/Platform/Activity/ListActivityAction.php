<?php

namespace App\Http\Actions\Api\V1\Platform\Activity;

use App\Services\Platform\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListActivityAction
{
    public function __construct(private ActivityLogService $activity) {}

    public function handle(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'tenant_id' => $request->input('tenant_id'),
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'loggable_type' => $request->input('loggable_type'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $perPage = (int) $request->input('per_page', 50);

        return response()->json($this->activity->list($filters, $perPage));
    }
}
