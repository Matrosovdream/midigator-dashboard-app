<?php

namespace App\Models\Concerns;

use App\Models\StageTransition;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWorkflowStages
{
    public function stageTransitions(): MorphMany
    {
        return $this->morphMany(StageTransition::class, 'trackable');
    }

    public function changeStage(string $newStage, ?int $userId = null, ?string $notes = null): self
    {
        $from = $this->stage;
        if ($from === $newStage) {
            return $this;
        }

        $this->stage = $newStage;
        $this->save();

        $this->stageTransitions()->create([
            'tenant_id' => $this->tenant_id,
            'from_stage' => $from,
            'to_stage' => $newStage,
            'user_id' => $userId,
            'notes' => $notes,
        ]);

        return $this;
    }
}
