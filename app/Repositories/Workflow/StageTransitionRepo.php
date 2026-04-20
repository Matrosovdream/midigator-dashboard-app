<?php

namespace App\Repositories\Workflow;

use App\Models\StageTransition;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;

class StageTransitionRepo extends AbstractRepo
{
    protected $withRelations = ['user'];

    public function __construct()
    {
        $this->model = new StageTransition();
    }

    public function record(Model $target, int $userId, string $fromStage, string $toStage, ?string $notes = null): array
    {
        return $this->create([
            'trackable_type' => $target->getMorphClass(),
            'trackable_id' => $target->getKey(),
            'user_id' => $userId,
            'from_stage' => $fromStage,
            'to_stage' => $toStage,
            'notes' => $notes,
        ]);
    }

    public function getForModel(Model $target)
    {
        $items = $this->model
            ->where('trackable_type', $target->getMorphClass())
            ->where('trackable_id', $target->getKey())
            ->with($this->withRelations)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return $this->mapItems($items);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'trackable_type' => $item->trackable_type,
            'trackable_id' => $item->trackable_id,
            'user_id' => $item->user_id,
            'user' => $item->relationLoaded('user') && $item->user
                ? ['id' => $item->user->id, 'name' => $item->user->name]
                : null,
            'from_stage' => $item->from_stage,
            'to_stage' => $item->to_stage,
            'notes' => $item->notes,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
