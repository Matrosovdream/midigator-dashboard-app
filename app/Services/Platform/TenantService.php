<?php

namespace App\Services\Platform;

use App\Models\Tenant;
use App\Repositories\Activity\ActivityLogRepo;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Order\OrderRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use App\Repositories\Tenant\TenantRepo;
use App\Repositories\User\UserRepo;
use App\Repositories\Webhook\WebhookLogRepo;
use App\Services\Midigator\AuthService as MidigatorAuthService;
use Throwable;

class TenantService
{
    public function __construct(
        private TenantRepo $tenantRepo,
        private UserRepo $userRepo,
        private ActivityLogRepo $activityLogRepo,
        private WebhookLogRepo $webhookLogRepo,
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionAlertRepo,
        private OrderRepo $orderRepo,
        private RdrCaseRepo $rdrCaseRepo,
        private MidigatorAuthService $midigatorAuth,
    ) {}

    public function list(array $filters = [], int $perPage = 20): array
    {
        $repoFilter = [];

        if (!empty($filters['search'])) {
            $repoFilter['search'] = $filters['search'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $repoFilter['is_active'] = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($filters['sandbox_mode']) && $filters['sandbox_mode'] !== null && $filters['sandbox_mode'] !== '') {
            $repoFilter['midigator_sandbox_mode'] = filter_var($filters['sandbox_mode'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->tenantRepo->getAll($repoFilter, $perPage, ['created_at' => 'desc']);
    }

    public function show(int $id): ?array
    {
        return $this->tenantRepo->getByID($id);
    }

    public function overview(int $id): ?array
    {
        $tenant = $this->tenantRepo->getByID($id);
        if (!$tenant) {
            return null;
        }

        $tenantFilter = ['tenant_id' => $id];

        $lastWebhook = $this->webhookLogRepo->getFirst($tenantFilter, ['created_at' => 'desc']);
        $lastFailedWebhook = $this->webhookLogRepo->getFirst(
            array_merge($tenantFilter, ['status' => 'failed']),
            ['created_at' => 'desc'],
        );

        $stats = [
            'users' => $this->userRepo->count($tenantFilter),
            'chargebacks' => $this->chargebackRepo->count($tenantFilter),
            'preventions' => $this->preventionAlertRepo->count($tenantFilter),
            'orders' => $this->orderRepo->count($tenantFilter),
            'rdr_cases' => $this->rdrCaseRepo->count($tenantFilter),
            'webhooks_total' => $this->webhookLogRepo->count($tenantFilter),
            'webhooks_failed' => $this->webhookLogRepo->count(array_merge($tenantFilter, ['status' => 'failed'])),
        ];

        unset($tenant['Model']);

        return [
            'tenant' => $tenant,
            'stats' => $stats,
            'last_webhook' => $lastWebhook ? $this->stripModel($lastWebhook) : null,
            'last_failed_webhook' => $lastFailedWebhook ? $this->stripModel($lastFailedWebhook) : null,
        ];
    }

    public function listUsers(int $tenantId, int $perPage = 20): array
    {
        return $this->userRepo->getByTenant($tenantId, $perPage, ['created_at' => 'desc']);
    }

    public function listActivity(int $tenantId, int $perPage = 50): array
    {
        return $this->activityLogRepo->getForTenant($tenantId, $perPage);
    }

    public function listWebhooks(int $tenantId, array $filters = [], int $perPage = 50): array
    {
        $repoFilter = [];
        if (!empty($filters['status'])) {
            $repoFilter['status'] = $filters['status'];
        }
        if (!empty($filters['event_type'])) {
            $repoFilter['event_type'] = $filters['event_type'];
        }
        return $this->webhookLogRepo->getForTenant($tenantId, $repoFilter, $perPage);
    }

    public function create(array $data): array
    {
        return $this->tenantRepo->create($this->stripBlankSecrets($data));
    }

    public function update(int $id, array $data): ?array
    {
        $data = $this->stripBlankSecrets($data);
        $result = $this->tenantRepo->update($id, $data);

        if ($result && (isset($data['midigator_sandbox_mode']) || isset($data['midigator_api_secret']))) {
            $this->midigatorAuth->forget($result['Model']);
        }

        return $result;
    }

    public function toggleActive(int $id): ?array
    {
        $tenant = $this->tenantRepo->getByID($id);
        if (!$tenant) {
            return null;
        }
        return $this->tenantRepo->update($id, ['is_active' => !$tenant['is_active']]);
    }

    public function delete(int $id): bool
    {
        return $this->tenantRepo->delete($id);
    }

    public function testConnection(array $data): array
    {
        $tenant = new Tenant([
            'midigator_api_secret' => $data['midigator_api_secret'] ?? null,
            'midigator_sandbox_mode' => (bool) ($data['midigator_sandbox_mode'] ?? false),
        ]);
        $tenant->id = $data['id'] ?? 0;

        try {
            $token = $this->midigatorAuth->getToken($tenant);
            return ['ok' => true, 'message' => 'Connection successful', 'token_preview' => substr($token, 0, 8).'…'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function stripBlankSecrets(array $data): array
    {
        foreach (['midigator_api_secret', 'midigator_webhook_password'] as $secret) {
            if (array_key_exists($secret, $data) && ($data[$secret] === '' || $data[$secret] === null)) {
                unset($data[$secret]);
            }
        }
        return $data;
    }

    private function stripModel(array $item): array
    {
        unset($item['Model']);
        return $item;
    }
}
