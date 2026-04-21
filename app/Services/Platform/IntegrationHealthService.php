<?php

namespace App\Services\Platform;

use App\Repositories\Tenant\TenantRepo;
use App\Repositories\Webhook\WebhookLogRepo;
use Illuminate\Support\Carbon;

class IntegrationHealthService
{
    public function __construct(
        private TenantRepo $tenantRepo,
        private WebhookLogRepo $webhookLogRepo,
    ) {}

    public function matrix(int $windowHours = 24): array
    {
        $since = Carbon::now()->subHours($windowHours);

        $aggregates = $this->webhookLogRepo->getTenantHealthMatrix($since);
        $tenants = $this->tenantRepo->getAll([], 200, ['name' => 'asc']);

        $rows = [];
        foreach (($tenants['items'] ?? []) as $tenant) {
            $agg = $aggregates[$tenant['id']] ?? ['total' => 0, 'failed' => 0, 'last_success_at' => null, 'last_failure_at' => null];

            $failureRate = $agg['total'] > 0 ? round($agg['failed'] / $agg['total'] * 100, 1) : 0;
            $status = $this->deriveStatus($tenant, $agg, $failureRate);

            $rows[] = [
                'tenant_id' => $tenant['id'],
                'tenant_name' => $tenant['name'],
                'tenant_slug' => $tenant['slug'],
                'is_active' => $tenant['is_active'],
                'sandbox_mode' => $tenant['midigator_sandbox_mode'],
                'has_api_secret' => $tenant['has_api_secret'],
                'total' => $agg['total'],
                'failed' => $agg['failed'],
                'failure_rate' => $failureRate,
                'last_success_at' => $agg['last_success_at'],
                'last_failure_at' => $agg['last_failure_at'],
                'status' => $status,
            ];
        }

        usort($rows, function ($a, $b) {
            $order = ['red' => 0, 'amber' => 1, 'grey' => 2, 'green' => 3];
            return ($order[$a['status']] ?? 9) <=> ($order[$b['status']] ?? 9);
        });

        return [
            'window_hours' => $windowHours,
            'rows' => $rows,
        ];
    }

    private function deriveStatus(array $tenant, array $agg, float $failureRate): string
    {
        if (!$tenant['is_active'] || !$tenant['has_api_secret']) {
            return 'grey';
        }
        if ($agg['total'] === 0) {
            return 'grey';
        }
        if ($failureRate >= 25) {
            return 'red';
        }
        if ($agg['failed'] > 0) {
            return 'amber';
        }
        return 'green';
    }
}
