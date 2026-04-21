<?php

namespace App\Services\Platform;

use App\Repositories\Activity\ActivityLogRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Order\OrderRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use App\Repositories\Tenant\TenantRepo;
use App\Repositories\User\UserRepo;
use App\Repositories\Webhook\WebhookLogRepo;
use Illuminate\Support\Carbon;

class OverviewService
{
    public function __construct(
        private TenantRepo $tenantRepo,
        private UserRepo $userRepo,
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionAlertRepo,
        private OrderRepo $orderRepo,
        private RdrCaseRepo $rdrCaseRepo,
        private WebhookLogRepo $webhookLogRepo,
        private ActivityLogRepo $activityLogRepo,
    ) {}

    public function summary(): array
    {
        $now = Carbon::now();
        $last30 = $now->copy()->subDays(30);
        $last7 = $now->copy()->subDays(7);
        $last24h = $now->copy()->subDay();

        $between30 = ['created_at' => ['CONDITION' => 'BETWEEN', 'from' => $last30, 'to' => $now]];
        $between24h = ['created_at' => ['CONDITION' => 'BETWEEN', 'from' => $last24h, 'to' => $now]];

        return [
            'tenants' => [
                'total' => $this->tenantRepo->count(),
                'active' => $this->tenantRepo->count(['is_active' => true]),
                'suspended' => $this->tenantRepo->count(['is_active' => false]),
            ],
            'users' => [
                'total' => $this->userRepo->count(),
                'active' => $this->userRepo->count(['is_active' => true]),
                'active_last_7d' => $this->userRepo->count([
                    'last_login_at' => ['CONDITION' => 'BETWEEN', 'from' => $last7, 'to' => $now],
                ]),
            ],
            'chargebacks' => [
                'total' => $this->chargebackRepo->count(),
                'last_30d' => $this->chargebackRepo->count($between30),
            ],
            'preventions' => [
                'total' => $this->preventionAlertRepo->count(),
                'last_30d' => $this->preventionAlertRepo->count($between30),
            ],
            'orders' => [
                'total' => $this->orderRepo->count(),
                'last_30d' => $this->orderRepo->count($between30),
            ],
            'rdr' => [
                'total' => $this->rdrCaseRepo->count(),
            ],
            'webhooks_24h' => [
                'total' => $this->webhookLogRepo->count($between24h),
                'failed' => $this->webhookLogRepo->count(array_merge($between24h, ['status' => 'failed'])),
            ],
            'recent_failed_webhooks' => $this->stripModels(
                $this->webhookLogRepo->getRecentFailed(10),
            ),
            'recent_events' => $this->stripModels(
                $this->activityLogRepo->getRecent(15)['items']->toArray() ?? [],
            ),
        ];
    }

    private function stripModels(array $items): array
    {
        return array_map(function ($item) {
            unset($item['Model']);
            return $item;
        }, $items);
    }
}
