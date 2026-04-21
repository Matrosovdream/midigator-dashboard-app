<?php

namespace App\Services\DummyData;

use App\Models\Tenant;
use App\Models\User;
use RuntimeException;

class TenantAssignmentPicker
{
    /** @var array<int, int> */
    private array $tenantIds = [];

    /** @var array<int, array<int, int>> */
    private array $tenantUserIds = [];

    private int $cursor = 0;

    public function __construct(?string $tenantSlug = null)
    {
        $query = Tenant::query()->where('is_active', true);

        if ($tenantSlug !== null) {
            $query->where('slug', $tenantSlug);
        }

        $this->tenantIds = $query->orderBy('id')->pluck('id')->all();

        if (empty($this->tenantIds)) {
            throw new RuntimeException('No active tenants found to attach dummy data to.');
        }

        foreach ($this->tenantIds as $tenantId) {
            $this->tenantUserIds[$tenantId] = User::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where('is_platform_admin', false)
                ->pluck('id')
                ->all();
        }
    }

    public function nextTenantId(): int
    {
        $tenantId = $this->tenantIds[$this->cursor % count($this->tenantIds)];
        $this->cursor++;
        return $tenantId;
    }

    public function randomAssigneeId(int $tenantId): ?int
    {
        $users = $this->tenantUserIds[$tenantId] ?? [];
        if (empty($users)) {
            return null;
        }
        return $users[array_rand($users)];
    }
}
