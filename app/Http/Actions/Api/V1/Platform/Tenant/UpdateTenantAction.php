<?php

namespace App\Http\Actions\Api\V1\Platform\Tenant;

use App\Services\Platform\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateTenantAction
{
    public function __construct(private TenantService $tenants) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('tenants', 'slug')->ignore($id)],
            'domain' => ['sometimes', 'nullable', 'string', 'max:255'],
            'midigator_api_secret' => ['sometimes', 'nullable', 'string'],
            'midigator_sandbox_mode' => ['sometimes', 'boolean'],
            'midigator_webhook_username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'midigator_webhook_password' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'nullable', 'array'],
        ]);

        $tenant = $this->tenants->update($id, $data);
        if (!$tenant) {
            abort(404, 'Tenant not found');
        }
        unset($tenant['Model']);

        return response()->json(['tenant' => $tenant]);
    }
}
