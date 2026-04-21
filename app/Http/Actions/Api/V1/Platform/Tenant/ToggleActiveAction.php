<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;

class ToggleActiveAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(int $id): JsonResponse
    {
        $tenant = $this->tenants->toggleActive($id);
        if (!$tenant) {
            abort(404, 'Tenant not found');
        }
        unset($tenant['Model']);
        return response()->json(['tenant' => $tenant]);
    }
}
