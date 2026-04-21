<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTenantUsersAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        return response()->json($this->tenants->listUsers($id, $perPage));
    }
}
