<?php

namespace App\Http\Actions\Api\V1\Order;

use App\Repositories\Tenant\TenantRepo;
use App\Services\Midigator\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitOrderAction
{
    public function __construct(
        private OrderService $orderService,
        private TenantRepo $tenantRepo,
    ) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $record = $this->tenantRepo->getByID($request->user()->tenant_id);
        abort_if($record === null, 403, 'Tenant not found.');

        $response = $this->orderService->submit($record['Model'], $id);

        return response()->json([
            'midigator_status' => $response->status(),
            'midigator_body' => $response->json(),
        ], $response->status());
    }
}
