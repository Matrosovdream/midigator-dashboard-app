<?php

namespace App\Http\Actions\Api\V1\Order;

use App\Repositories\Order\OrderRepo;
use Illuminate\Http\JsonResponse;

class ShowOrderAction
{
    public function __construct(private OrderRepo $orderRepo) {}

    public function handle(int $id): JsonResponse
    {
        $record = $this->orderRepo->getByID($id);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
