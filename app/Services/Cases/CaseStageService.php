<?php

namespace App\Services\Cases;

use App\Events\StageChanged;
use App\Repositories\Workflow\StageTransitionRepo;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CaseStageService
{
    public function __construct(
        private CaseRegistry $registry,
        private StageTransitionRepo $stageTransitionRepo,
    ) {}

    public function changeStage(string $caseType, int $caseId, string $newStage, int $userId, ?string $notes = null): array
    {
        $repo = $this->registry->repoFor($caseType);
        $record = $repo->getByID($caseId);
        if (!$record) {
            throw new RuntimeException("Case $caseType#$caseId not found.");
        }

        /** @var Model $model */
        $model = $record['Model'];
        $fromStage = (string) $model->stage;

        if ($fromStage === $newStage) {
            return $record;
        }

        $updated = $repo->update($caseId, ['stage' => $newStage]);
        $this->stageTransitionRepo->record($model, $userId, $fromStage, $newStage, $notes);

        StageChanged::dispatch($model->tenant_id, $caseType, $caseId, $fromStage, $newStage, $userId);

        return $updated ?? $record;
    }
}
