<?php

namespace App\Http\Actions\Api\V1\Order;

use App\Repositories\Order\OrderRepo;
use App\Repositories\Tenant\TenantRepo;
use App\Services\Midigator\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitOrderAction
{
    public function __construct(
        private OrderService $orderService,
        private TenantRepo $tenantRepo,
        private OrderRepo $orderRepo,
    ) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $record = $this->tenantRepo->getByID($request->user()->tenant_id);
        abort_if($record === null, 403, 'Tenant not found.');

        $response = $this->orderService->submit($record['Model'], $id);
        $refreshed = $this->orderRepo->getByID($id);

        if (isset($refreshed['Model'])) {
            unset($refreshed['Model']);
        }

        return response()->json([
            'record' => $refreshed,
            'midigator_status' => $response->status(),
            'midigator_body' => $response->json(),
        ], $response->status());
    }
}
