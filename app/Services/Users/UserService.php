<?php

namespace App\Services\Users;

use App\Repositories\User\UserRepo;

class UserService
{
    public function __construct(private UserRepo $userRepo) {}

    public function listForTenant(?int $tenantId, int $perPage = 20): array
    {
        if ($tenantId === null) {
            return $this->userRepo->getAll([], $perPage, ['created_at' => 'desc']);
        }

        return $this->userRepo->getByTenant($tenantId, $perPage, ['created_at' => 'desc']);
    }
}
