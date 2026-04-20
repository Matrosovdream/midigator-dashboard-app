<?php

namespace App\Repositories\Webhook;

use App\Models\WebhookLog;
use App\Repositories\AbstractRepo;

class WebhookLogRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new WebhookLog();
    }

    public function getByEventGuid(string $eventGuid)
    {
        $item = $this->model->where('event_guid', $eventGuid)->first();
        return $this->mapItem($item);
    }

    public function getForTenant(int $tenantId, array $filter = [], $paginate = 50)
    {
        return $this->getAll(
            array_merge(['tenant_id' => $tenantId], $filter),
            $paginate,
            ['created_at' => 'desc'],
        );
    }

    public function markProcessed(int $id): ?array
    {
        return $this->update($id, [
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function markFailed(int $id, string $errorMessage): ?array
    {
        return $this->update($id, [
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'event_type' => $item->event_type,
            'event_guid' => $item->event_guid,
            'status' => $item->status,
            'error_message' => $item->error_message,
            'processed_at' => $item->processed_at,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
