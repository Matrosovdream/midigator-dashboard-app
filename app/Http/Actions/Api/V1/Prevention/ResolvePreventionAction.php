<?php

namespace App\Http\Actions\Api\V1\Prevention;

use App\Repositories\Tenant\TenantRepo;
use App\Services\Midigator\PreventionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResolvePreventionAction
{
    public function __construct(
        private PreventionService $preventionService,
        private TenantRepo $tenantRepo,
    ) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'resolution_type' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $record = $this->tenantRepo->getByID($request->user()->tenant_id);
        abort_if($record === null, 403, 'Tenant not found.');

        $response = $this->preventionService->submitResolution(
            $record['Model'],
            $id,
            $data['resolution_type'],
            $data['description'] ?? null,
        );

        return response()->json([
            'midigator_status' => $response->status(),
            'midigator_body' => $response->json(),
        ], $response->status());
    }
}
