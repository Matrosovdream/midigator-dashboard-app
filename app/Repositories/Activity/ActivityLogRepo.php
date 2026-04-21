<?php

namespace App\Repositories\Activity;

use App\Models\ActivityLog;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;

class ActivityLogRepo extends AbstractRepo
{
    protected $withRelations = ['user'];

    public function __construct()
    {
        $this->model = new ActivityLog();
    }

    public function log(int $tenantId, int $userId, string $action, ?Model $loggable = null, array $metadata = []): array
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'loggable_type' => $loggable ? $loggable->getMorphClass() : '',
            'loggable_id' => $loggable?->getKey(),
            'action' => $action,
            'metadata' => $metadata ?: null,
        ]);
    }

    public function getForTenant(int $tenantId, $paginate = 50)
    {
        return $this->getAll(['tenant_id' => $tenantId], $paginate, ['created_at' => 'desc']);
    }

    public function getRecent(int $perPage = 20): array
    {
        $items = $this->model
            ->with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->mapItems($items);
    }

    public function getAllCrossTenant(array $filter = [], int $perPage = 50): array
    {
        $search = $filter['search'] ?? null;
        unset($filter['search']);

        $query = $this->model->with(['user', 'tenant']);
        $query = $this->applyFilter($query, $filter);

        if (!empty($search)) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('action', 'LIKE', $like)
                    ->orWhere('loggable_type', 'LIKE', $like);
            });
        }

        $query->orderBy('created_at', 'desc');

        return $this->mapItems($query->paginate($perPage));
    }

    public function getForModel(Model $target)
    {
        $items = $this->model
            ->where('loggable_type', $target->getMorphClass())
            ->where('loggable_id', $target->getKey())
            ->with($this->withRelations)
            ->orderBy('created_at', 'desc')
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
            'tenant_id' => $item->tenant_id,
            'user_id' => $item->user_id,
            'user' => $item->relationLoaded('user') && $item->user
                ? ['id' => $item->user->id, 'name' => $item->user->name]
                : null,
            'tenant' => $item->relationLoaded('tenant') && $item->tenant
                ? ['id' => $item->tenant->id, 'name' => $item->tenant->name]
                : null,
            'loggable_type' => $item->loggable_type,
            'loggable_id' => $item->loggable_id,
            'action' => $item->action,
            'metadata' => $item->metadata ?? [],
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
