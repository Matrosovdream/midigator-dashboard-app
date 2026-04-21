<?php

namespace App\Services\Platform;

use App\Repositories\Email\EmailLogRepo;

class EmailLogService
{
    public function __construct(private EmailLogRepo $emailLogRepo) {}

    public function list(array $filters = [], int $perPage = 25): array
    {
        $repoFilter = [];
        if (!empty($filters['search'])) {
            $repoFilter['search'] = $filters['search'];
        }
        if (!empty($filters['tenant_id'])) {
            $repoFilter['tenant_id'] = (int) $filters['tenant_id'];
        }
        if (!empty($filters['status'])) {
            $repoFilter['status'] = $filters['status'];
        }
        return $this->emailLogRepo->getAllCrossTenant($repoFilter, $perPage);
    }

    public function show(int $id): ?array
    {
        $log = $this->emailLogRepo->getByIDWithBody($id);
        if (!$log) {
            return null;
        }
        unset($log['Model']);
        return $log;
    }
}
