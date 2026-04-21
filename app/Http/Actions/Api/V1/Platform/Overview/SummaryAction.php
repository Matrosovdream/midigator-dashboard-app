<?php

namespace App\Http\Actions\Api\V1\Platform\Overview;

use App\Services\Platform\OverviewService;
use Illuminate\Http\JsonResponse;

class SummaryAction
{
    public function __construct(private OverviewService $overview) {}

    public function handle(): JsonResponse
    {
        return response()->json($this->overview->summary());
    }
}
