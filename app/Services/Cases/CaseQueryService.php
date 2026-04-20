<?php

namespace App\Services\Cases;

use App\Models\User;

class CaseQueryService
{
    public function __construct(private CaseRegistry $registry) {}

    public function list(string $caseType, User $user, array $filter = [], int $perPage = 25, array $sorting = []): ?array
    {
        $repo = $this->registry->repoFor($caseType);

        if (!$user->hasRight("$caseType"."s.hide") && !$user->isPlatformAdmin()) {
            $filter['is_hidden'] = false;
        }

        return $repo->getAll(
            array_merge(['tenant_id' => $user->tenant_id], $filter),
            $perPage,
            $sorting ?: ['created_at' => 'desc'],
        );
    }

    public function get(string $caseType, int $id): ?array
    {
        return $this->registry->repoFor($caseType)->getByID($id);
    }
}
