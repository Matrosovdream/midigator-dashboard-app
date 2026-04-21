<?php

namespace App\Services\Dashboard;

use App\Repositories\Activity\ActivityLogRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Order\OrderRepo;
use App\Repositories\Order\OrderValidationRepo;
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
        private OrderRepo $orderRepo,
        private OrderValidationRepo $orderValidationRepo,
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
            $last30dStart = now()->subDays(30);
            $slaCutoff = now()->addDays(2);

            $openStages = ['new', 'in_review', 'action_taken', 'responded'];
            $activeAlertStages = ['new', 'in_review'];

            $openChargebacks = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', ...$openStages]]);
            $activeAlerts = $this->preventionRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', ...$activeAlertStages]]);
            $openRdr = $this->rdrRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', ...$activeAlertStages]]);

            $chargebacksThisMonth = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $monthStart, now()]]);
            $chargebacksLastMonth = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $prevMonthStart, $prevMonthEnd]]);
            $chargebacksLast30d = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $last30dStart, now()]]);
            $preventionsLast30d = $this->preventionRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $last30dStart, now()]]);
            $ordersLast30d = $this->orderRepo->count(['tenant_id' => $tenantId, 'created_at' => ['CONDITION' => 'BETWEEN', $last30dStart, now()]]);

            $pendingOrders = $this->orderRepo->count(['tenant_id' => $tenantId, 'refunded' => 0, 'is_hidden' => 0]);
            $validationsPending = $this->orderValidationRepo->count(['tenant_id' => $tenantId, 'stage' => ['CONDITION' => 'IN', ...$activeAlertStages]]);

            $slaAtRisk = (int) $this->chargebackRepo->getModel()
                ->where('tenant_id', $tenantId)
                ->whereIn('stage', $openStages)
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [now(), $slaCutoff])
                ->count();

            $won = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'result' => 'won']);
            $lost = $this->chargebackRepo->count(['tenant_id' => $tenantId, 'result' => 'lost']);
            $resolved = $won + $lost;

            $amountAtRisk = (int) $this->chargebackRepo->getModel()
                ->where('tenant_id', $tenantId)
                ->where('result', 'pending')
                ->sum('amount');

            $recentActivity = $this->activityLogRepo->getForTenant($tenantId, 10)['items'] ?? [];

            return [
                'tenant_id' => $tenantId,
                'chargebacks' => [
                    'open' => $openChargebacks,
                    'this_month' => $chargebacksThisMonth,
                    'last_month' => $chargebacksLastMonth,
                    'last_30d' => $chargebacksLast30d,
                    'sla_at_risk' => $slaAtRisk,
                    'amount_at_risk' => $amountAtRisk,
                    'trend_pct' => $chargebacksLastMonth ? round((($chargebacksThisMonth - $chargebacksLastMonth) / $chargebacksLastMonth) * 100, 1) : null,
                ],
                'preventions' => [
                    'open' => $activeAlerts,
                    'last_30d' => $preventionsLast30d,
                ],
                'rdr' => [
                    'open' => $openRdr,
                    'total' => $this->rdrRepo->count(['tenant_id' => $tenantId]),
                ],
                'orders' => [
                    'pending' => $pendingOrders,
                    'last_30d' => $ordersLast30d,
                    'total' => $this->orderRepo->count(['tenant_id' => $tenantId]),
                ],
                'validations' => [
                    'pending' => $validationsPending,
                ],
                'win_rate' => $resolved ? round(($won / $resolved) * 100, 1) : null,
                'resolved' => $resolved,
                'recent_activity' => $recentActivity,
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
            'chargebacks' => ['open' => 0, 'this_month' => 0, 'last_month' => 0, 'last_30d' => 0, 'sla_at_risk' => 0, 'amount_at_risk' => 0, 'trend_pct' => null],
            'preventions' => ['open' => 0, 'last_30d' => 0],
            'rdr' => ['open' => 0, 'total' => 0],
            'orders' => ['pending' => 0, 'last_30d' => 0, 'total' => 0],
            'validations' => ['pending' => 0],
            'win_rate' => null,
            'resolved' => 0,
            'recent_activity' => [],
        ];
    }
}
