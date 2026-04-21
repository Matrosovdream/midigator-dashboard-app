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

    public function getAllCrossTenant(array $filter = [], int $perPage = 25): array
    {
        $query = $this->model->with('tenant');
        $query = $this->applyFilter($query, $filter);
        $query->orderBy('created_at', 'desc');

        return $this->mapItems($query->paginate($perPage));
    }

    public function getTenantHealthMatrix(\DateTimeInterface $since): array
    {
        $rows = $this->model
            ->selectRaw('tenant_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed,
                MAX(CASE WHEN status = ? THEN created_at END) as last_success_at,
                MAX(CASE WHEN status = ? THEN created_at END) as last_failure_at',
                ['failed', 'processed', 'failed'],
            )
            ->where('created_at', '>=', $since)
            ->groupBy('tenant_id')
            ->get();

        return $rows->map(fn ($r) => [
            'tenant_id' => (int) $r->tenant_id,
            'total' => (int) $r->total,
            'failed' => (int) $r->failed,
            'last_success_at' => $r->last_success_at,
            'last_failure_at' => $r->last_failure_at,
        ])->keyBy('tenant_id')->toArray();
    }

    public function getRecentFailed(int $limit = 10): array
    {
        $items = $this->model
            ->where('status', 'failed')
            ->with('tenant')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => $this->mapItem($item))
            ->toArray();

        return $items;
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
            'tenant' => $item->relationLoaded('tenant') && $item->tenant
                ? ['id' => $item->tenant->id, 'name' => $item->tenant->name]
                : null,
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
