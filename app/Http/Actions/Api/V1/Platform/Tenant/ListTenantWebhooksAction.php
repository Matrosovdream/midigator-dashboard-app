<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTenantWebhooksAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 25);
        $filters = [
            'status' => $request->input('status'),
            'event_type' => $request->input('event_type'),
        ];
        return response()->json($this->tenants->listWebhooks($id, $filters, $perPage));
    }
}
