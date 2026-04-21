<?php

namespace App\Services\Platform;

use App\Repositories\Webhook\WebhookLogRepo;

class WebhookLogService
{
    public function __construct(private WebhookLogRepo $webhookLogRepo) {}

    public function list(array $filters = [], int $perPage = 25): array
    {
        $repoFilter = [];

        if (!empty($filters['tenant_id'])) {
            $repoFilter['tenant_id'] = (int) $filters['tenant_id'];
        }
        if (!empty($filters['status'])) {
            $repoFilter['status'] = $filters['status'];
        }
        if (!empty($filters['event_type'])) {
            $repoFilter['event_type'] = $filters['event_type'];
        }

        return $this->webhookLogRepo->getAllCrossTenant($repoFilter, $perPage);
    }

    public function show(int $id): ?array
    {
        $log = $this->webhookLogRepo->setRelations(['tenant'])->getByID($id);
        if (!$log) {
            return null;
        }
        $log['payload'] = $log['Model']->payload ?? null;
        unset($log['Model']);
        return $log;
    }
}
