<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreTenantAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug'],
            'domain' => ['nullable', 'string', 'max:255'],
            'midigator_api_secret' => ['nullable', 'string'],
            'midigator_sandbox_mode' => ['boolean'],
            'midigator_webhook_username' => ['nullable', 'string', 'max:255'],
            'midigator_webhook_password' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        $tenant = $this->tenants->create($data);
        unset($tenant['Model']);

        return response()->json(['tenant' => $tenant], 201);
    }
}
