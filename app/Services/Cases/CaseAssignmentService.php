<?php

namespace App\Services\Cases;

use App\Events\CaseAssigned;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CaseAssignmentService
{
    public function __construct(private CaseRegistry $registry) {}

    public function assign(string $caseType, int $caseId, ?int $userId, int $actorId): array
    {
        $repo = $this->registry->repoFor($caseType);
        $record = $repo->getByID($caseId);
        if (!$record) {
            throw new RuntimeException("Case $caseType#$caseId not found.");
        }

        $updated = $repo->update($caseId, ['assigned_to' => $userId]) ?? $record;

        /** @var Model $model */
        $model = $updated['Model'];

        CaseAssigned::dispatch($model->tenant_id, $caseType, $caseId, $userId, $actorId);

        return $updated;
    }
}
