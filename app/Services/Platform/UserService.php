<?php

namespace App\Services\Platform;

use App\Repositories\User\UserRepo;
use RuntimeException;

class UserService
{
    public function __construct(private UserRepo $userRepo) {}

    public function list(array $filters = [], int $perPage = 20): array
    {
        $repoFilter = [];

        if (!empty($filters['search'])) {
            $repoFilter['search'] = $filters['search'];
        }

        if (!empty($filters['tenant_id'])) {
            $repoFilter['tenant_id'] = (int) $filters['tenant_id'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $repoFilter['is_active'] = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($filters['is_platform_admin']) && $filters['is_platform_admin'] !== null && $filters['is_platform_admin'] !== '') {
            $repoFilter['is_platform_admin'] = filter_var($filters['is_platform_admin'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->userRepo->getAllWithTenant($repoFilter, $perPage, ['created_at' => 'desc']);
    }

    public function toggleActive(int $id, int $actorId): ?array
    {
        if ($id === $actorId) {
            throw new RuntimeException('You cannot deactivate your own account here.');
        }
        $user = $this->userRepo->getByID($id);
        if (!$user) {
            return null;
        }
        return $this->userRepo->update($id, ['is_active' => !$user['is_active']]);
    }

    public function togglePlatformAdmin(int $id, int $actorId): ?array
    {
        if ($id === $actorId) {
            throw new RuntimeException('You cannot change your own platform admin flag.');
        }
        $user = $this->userRepo->getByID($id);
        if (!$user) {
            return null;
        }
        return $this->userRepo->update($id, ['is_platform_admin' => !$user['is_platform_admin']]);
    }
}
