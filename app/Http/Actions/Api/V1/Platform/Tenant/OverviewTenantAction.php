<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;

class OverviewTenantAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(int $id): JsonResponse
    {
        $data = $this->tenants->overview($id);
        if (!$data) {
            abort(404, 'Tenant not found');
        }
        return response()->json($data);
    }
}
