<?php

namespace App\Services\Cases;

class CaseHideService
{
    public function __construct(private CaseRegistry $registry) {}

    public function setHidden(string $caseType, int $caseId, bool $hidden): ?array
    {
        $repo = $this->registry->repoFor($caseType);
        return $repo->update($caseId, ['is_hidden' => $hidden]);
    }

    public function bulkSetHidden(string $caseType, array $ids, bool $hidden): int
    {
        $repo = $this->registry->repoFor($caseType);
        $count = 0;
        foreach ($ids as $id) {
            if ($repo->update((int) $id, ['is_hidden' => $hidden])) {
                $count++;
            }
        }
        return $count;
    }
}
