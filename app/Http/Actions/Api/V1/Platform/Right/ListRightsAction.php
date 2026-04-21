<?php

namespace App\Http\Actions\Api\V1\Platform\Right;

use App\Services\Platform\RightsCatalogService;
use Illuminate\Http\JsonResponse;

class ListRightsAction
{
    public function __construct(private RightsCatalogService $rights) {}

    public function handle(): JsonResponse
    {
        return response()->json($this->rights->list());
    }
}
