<?php

namespace App\Http\Actions\Api\V1\Platform\Webhook;

use App\Services\Platform\WebhookLogService;
use Illuminate\Http\JsonResponse;

class ShowWebhookAction
{
    public function __construct(private WebhookLogService $webhooks) {}

    public function handle(int $id): JsonResponse
    {
        $log = $this->webhooks->show($id);
        if (!$log) {
            abort(404, 'Webhook log not found');
        }
        return response()->json(['webhook' => $log]);
    }
}
