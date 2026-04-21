<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTenantsAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'is_active' => $request->input('is_active'),
            'sandbox_mode' => $request->input('sandbox_mode'),
        ];

        $perPage = (int) $request->input('per_page', 20);

        return response()->json($this->tenants->list($filters, $perPage));
    }
}
