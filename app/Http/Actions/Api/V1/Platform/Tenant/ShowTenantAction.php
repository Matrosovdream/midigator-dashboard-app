<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;

class ShowTenantAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(int $id): JsonResponse
    {
        $tenant = $this->tenants->show($id);
        if (!$tenant) {
            abort(404, 'Tenant not found');
        }
        return response()->json(['tenant' => $this->presentable($tenant)]);
    }

    private function presentable(array $t): array
    {
        unset($t['Model']);
        return $t;
    }
}
