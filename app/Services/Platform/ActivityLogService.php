<?php

namespace App\Services\Platform;

use App\Repositories\Activity\ActivityLogRepo;
use Illuminate\Support\Carbon;

class ActivityLogService
{
    public function __construct(private ActivityLogRepo $activityLogRepo) {}

    public function list(array $filters = [], int $perPage = 50): array
    {
        $repoFilter = [];

        if (!empty($filters['search'])) {
            $repoFilter['search'] = $filters['search'];
        }
        if (!empty($filters['tenant_id'])) {
            $repoFilter['tenant_id'] = (int) $filters['tenant_id'];
        }
        if (!empty($filters['user_id'])) {
            $repoFilter['user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $repoFilter['action'] = $filters['action'];
        }
        if (!empty($filters['loggable_type'])) {
            $repoFilter['loggable_type'] = $filters['loggable_type'];
        }

        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        if ($from || $to) {
            $repoFilter['created_at'] = [
                'CONDITION' => 'BETWEEN',
                'from' => $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subYears(5),
                'to' => $to ? Carbon::parse($to)->endOfDay() : Carbon::now(),
            ];
        }

        return $this->activityLogRepo->getAllCrossTenant($repoFilter, $perPage);
    }
}
