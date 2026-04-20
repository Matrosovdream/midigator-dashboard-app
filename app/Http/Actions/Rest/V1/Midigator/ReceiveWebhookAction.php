<?php

namespace App\Http\Actions\Rest\V1\Midigator;

use App\Jobs\Midigator\ProcessMidigatorWebhookJob;
use App\Models\Tenant;
use App\Repositories\Webhook\WebhookLogRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiveWebhookAction
{
    public function __construct(private WebhookLogRepo $webhookLogRepo) {}

    public function handle(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('webhook_tenant');

        $data = $request->validate([
            'event_type' => ['required', 'string'],
            'event_guid' => ['nullable', 'string'],
        ]);

        $payload = $request->all();
        $eventGuid = $data['event_guid'] ?? ($payload['event_guid'] ?? (string) str()->uuid());

        $log = $this->webhookLogRepo->create([
            'tenant_id' => $tenant->id,
            'event_type' => $data['event_type'],
            'event_guid' => $eventGuid,
            'payload' => $payload,
        ]);

        ProcessMidigatorWebhookJob::dispatch($log['id'], $tenant->id, $data['event_type'], $payload);

        return response()->json(['received' => true, 'log_id' => $log['id']], 202);
    }
}
