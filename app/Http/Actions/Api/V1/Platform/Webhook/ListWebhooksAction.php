<?php

namespace App\Http\Actions\Api\V1\Platform\Webhook;

use App\Services\Platform\WebhookLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListWebhooksAction
{
    public function __construct(private WebhookLogService $webhooks) {}

    public function handle(Request $request): JsonResponse
    {
        $filters = [
            'tenant_id' => $request->input('tenant_id'),
            'status' => $request->input('status'),
            'event_type' => $request->input('event_type'),
        ];
        $perPage = (int) $request->input('per_page', 25);
        return response()->json($this->webhooks->list($filters, $perPage));
    }
}
