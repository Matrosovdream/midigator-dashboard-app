<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;

class DeleteTenantAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(int $id): JsonResponse
    {
        $ok = $this->tenants->delete($id);
        if (!$ok) {
            abort(404, 'Tenant not found');
        }
        return response()->json(['ok' => true]);
    }
}
