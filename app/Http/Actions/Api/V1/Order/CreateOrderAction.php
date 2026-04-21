<?php

namespace App\Http\Actions\Api\V1\Order;

use App\Repositories\Tenant\TenantRepo;
use App\Services\Midigator\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateOrderAction
{
    public function __construct(
        private OrderService $orderService,
        private TenantRepo $tenantRepo,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string', 'max:255'],
            'mid' => ['required', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'order_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'card_brand' => ['nullable', 'string', 'max:50'],
            'card_first_6' => ['nullable', 'string', 'size:6'],
            'card_last_4' => ['nullable', 'string', 'size:4'],
            'billing_first_name' => ['nullable', 'string', 'max:100'],
            'billing_last_name' => ['nullable', 'string', 'max:100'],
            'billing_address' => ['nullable', 'array'],
            'processor_auth_code' => ['nullable', 'string', 'max:100'],
            'processor_transaction_id' => ['nullable', 'string', 'max:100'],
            'refunded' => ['nullable', 'boolean'],
            'refunded_amount' => ['nullable', 'integer', 'min:0'],
            'items' => ['nullable', 'array'],
        ]);

        $record = $this->tenantRepo->getByID($request->user()->tenant_id);
        abort_if($record === null, 403, 'Tenant not found.');

        $result = $this->orderService->createAndSubmit($record['Model'], $data);

        if (isset($result['record']['Model'])) {
            unset($result['record']['Model']);
        }

        $status = $result['midigator_status'] >= 200 && $result['midigator_status'] < 300 ? 201 : 202;
        return response()->json($result, $status);
    }
}
