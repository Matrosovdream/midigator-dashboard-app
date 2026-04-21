<?php

namespace App\Services\Platform;

use App\Repositories\User\RightRepo;

class RightsCatalogService
{
    public function __construct(private RightRepo $rightRepo) {}

    public function list(): array
    {
        $rights = $this->rightRepo->getAllWithRoleCount();

        $groups = [];
        foreach ($rights as $right) {
            $group = $right['group'] ?? 'other';
            if (!isset($groups[$group])) {
                $groups[$group] = [
                    'name' => $group,
                    'rights' => [],
                ];
            }
            unset($right['Model']);
            $groups[$group]['rights'][] = $right;
        }

        return [
            'groups' => array_values($groups),
            'total' => count($rights),
        ];
    }
}
