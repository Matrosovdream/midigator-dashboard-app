<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTenantActivityAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        return response()->json($this->tenants->listActivity($id, $perPage));
    }
}
