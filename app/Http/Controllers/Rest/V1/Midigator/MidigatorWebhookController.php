<?php

namespace App\Http\Controllers\Rest\V1\Midigator;

use App\Http\Actions\Rest\V1\Midigator\ReceiveWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidigatorWebhookController extends Controller
{
    public function handle(Request $request, ReceiveWebhookAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
