<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Actions\Api\V1\Order\CreateOrderAction;
use App\Http\Actions\Api\V1\Order\ListOrdersAction;
use App\Http\Actions\Api\V1\Order\ShowOrderAction;
use App\Http\Actions\Api\V1\Order\SubmitOrderAction;
use App\Http\Actions\Api\V1\Order\UpdateOrderAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, ListOrdersAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function store(Request $request, CreateOrderAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function show(int $id, ShowOrderAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function update(Request $request, int $id, UpdateOrderAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }

    public function submit(Request $request, int $id, SubmitOrderAction $action): JsonResponse
    {
        return $action->handle($request, $id);
    }
}
