<?php

namespace App\Http\Actions\Api\V1\Order;

use App\Repositories\Order\OrderRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateOrderAction
{
    public function __construct(private OrderRepo $orderRepo) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'refunded' => ['nullable', 'boolean'],
            'refunded_amount' => ['nullable', 'integer'],
            'is_hidden' => ['nullable', 'boolean'],
            'evidence' => ['nullable', 'array'],
        ]);

        $record = $this->orderRepo->update($id, $data);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
