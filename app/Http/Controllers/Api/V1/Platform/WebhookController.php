<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Actions\Api\V1\Platform\Webhook\ListWebhooksAction;
use App\Http\Actions\Api\V1\Platform\Webhook\ShowWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request, ListWebhooksAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function show(int $id, ShowWebhookAction $action): JsonResponse
    {
        return $action->handle($id);
    }
}
