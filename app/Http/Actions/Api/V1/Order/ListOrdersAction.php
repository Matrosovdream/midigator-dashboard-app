<?php

namespace App\Http\Actions\Api\V1\Order;

use App\Repositories\Order\OrderRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListOrdersAction
{
    public function __construct(private OrderRepo $orderRepo) {}

    public function handle(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->integer('per_page', 25);
        $filter = array_merge(
            (array) $request->input('filter', []),
            ['tenant_id' => $user->tenant_id],
        );

        if (!$user->hasRight('orders.hide') && !$user->isPlatformAdmin()) {
            $filter['is_hidden'] = false;
        }

        return response()->json(
            $this->orderRepo->getAll($filter, $perPage, ['created_at' => 'desc']),
        );
    }
}
