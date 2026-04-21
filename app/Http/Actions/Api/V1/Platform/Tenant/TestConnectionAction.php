<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestConnectionAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:tenants,id'],
            'midigator_api_secret' => ['nullable', 'string'],
            'midigator_sandbox_mode' => ['boolean'],
        ]);

        // If editing an existing tenant and secret blank, reuse stored secret
        if (!empty($data['id']) && empty($data['midigator_api_secret'])) {
            $existing = $this->tenants->show((int) $data['id']);
            if ($existing && $existing['Model']->midigator_api_secret) {
                $data['midigator_api_secret'] = $existing['Model']->midigator_api_secret;
            }
        }

        return response()->json($this->tenants->testConnection($data));
    }
}
