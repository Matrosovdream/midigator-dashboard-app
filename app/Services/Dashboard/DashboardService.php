<?php

namespace App\Services\Dashboard;

use App\Repositories\Activity\ActivityLogRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use App\Repositories\User\ManagerProfileRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionRepo,
        private RdrCaseRepo $rdrRepo,
        private UserRepo $userRepo,
        private ManagerProfileRepo $managerProfileRepo,
        private ActivityLogRepo $activityLogRepo,
    ) {}

    public function summaryForTenant(?int $tenantId): array
    {
        if ($tenantId === null) {
            return $this->emptySummary();
        }

        return Cache::remember("dashboard:summary:$tenantId", 300, function () use ($tenantId) {
            $monthStart = now()->startOfMonth();
            $prevMonthStart = now()->subMonth()->startOfMonth();
            $prevMonthEnd = now()->startOfMonth();

            $openStages = ['new', 'in_review', 'action_taken', 'responded'];
            $openChargebacks = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', ...$openStages]]);
            $activeAlerts = $this->preventionRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', 'new', 'in_review']]);
            $openRdr = $this->rdrRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', 'new', 'in_review']]);

            $thisMonth = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $monthStart, now()]]);
            $lastMonth = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $prevMonthStart, $prevMonthEnd]]);

            $won = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'result' => 'won']);
            $lost = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'result' => 'lost']);
            $resolved = $won + $lost;

            $amountAtRisk = (int) $this->chargebackRepo->getModel()
                ->where('tenant_id', $tenantId)
                ->where('result', 'pending')
                ->sum('amount');

            return [
                'tenant_id' => $tenantId,
                'open_chargebacks' => $openChargebacks,
                'active_alerts' => $activeAlerts,
                'open_rdr_cases' => $openRdr,
                'chargebacks_this_month' => $thisMonth,
                'chargebacks_last_month' => $lastMonth,
                'trend_pct' => $lastMonth ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : null,
                'win_rate' => $resolved ? round(($won / $resolved) * 100, 1) : null,
                'resolved' => $resolved,
                'amount_at_risk' => $amountAtRisk,
            ];
        });
    }

    public function managerPerformance(int $tenantId, int $perPage = 25): array
    {
        return Cache::remember("dashboard:manager-performance:$tenantId", 300, function () use ($tenantId, $perPage) {
            $managers = $this->userRepo->getByTenant($tenantId, $perPage);
            $items = ($managers['items'] ?? collect())->map(function ($user) {
                $profile = $this->managerProfileRepo->getByUserID($user['id']);
                $handled = (int) DB::table('chargebacks')->where('tenant_id', $user['tenant_id'])->where('assigned_to', $user['id'])->count();
                $won = (int) DB::table('chargebacks')->where('tenant_id', $user['tenant_id'])->where('assigned_to', $user['id'])->where('result', 'won')->count();
                $lost = (int) DB::table('chargebacks')->where('tenant_id', $user['tenant_id'])->where('assigned_to', $user['id'])->where('result', 'lost')->count();
                $resolved = $won + $lost;

                return [
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'score' => $profile['score'] ?? null,
                    'cases_handled' => $handled,
                    'win_rate' => $resolved ? round(($won / $resolved) * 100, 1) : null,
                ];
            });

            return ['items' => $items->all()];
        });
    }

    public function recentActivity(int $tenantId, int $limit = 20): array
    {
        $items = $this->activityLogRepo->getAll(['tenant_id' => $tenantId], $limit, ['created_at' => 'desc']);
        return $items ?? ['items' => []];
    }

    private function emptySummary(): array
    {
        return [
            'tenant_id' => null,
            'open_chargebacks' => 0,
            'active_alerts' => 0,
            'open_rdr_cases' => 0,
            'chargebacks_this_month' => 0,
            'chargebacks_last_month' => 0,
            'trend_pct' => null,
            'win_rate' => null,
            'resolved' => 0,
            'amount_at_risk' => 0,
        ];
    }
}
