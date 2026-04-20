<?php

namespace App\Services\Activity;

use App\Repositories\Activity\ActivityLogRepo;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function __construct(private ActivityLogRepo $repo) {}

    public function log(int $tenantId, ?int $userId, string $action, ?Model $loggable = null, array $metadata = []): ?array
    {
        if ($userId === null) {
            return null;
        }
        return $this->repo->log($tenantId, $userId, $action, $loggable, $metadata);
    }

    public function listForTenant(int $tenantId, int $perPage = 50, array $filter = []): ?array
    {
        return $this->repo->getAll(array_merge(['tenant_id' => $tenantId], $filter), $perPage, ['created_at' => 'desc']);
    }

    public function listForModel(Model $target): ?array
    {
        return $this->repo->getForModel($target);
    }
}
