<?php

namespace App\Repositories\Comment;

use App\Models\Comment;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;

class CommentRepo extends AbstractRepo
{
    protected $withRelations = ['user'];

    public function __construct()
    {
        $this->model = new Comment();
    }

    public function addToModel(Model $target, int $userId, string $body, bool $isInternal = false): array
    {
        return $this->create([
            'commentable_type' => $target->getMorphClass(),
            'commentable_id' => $target->getKey(),
            'user_id' => $userId,
            'body' => $body,
            'is_internal' => $isInternal,
        ]);
    }

    public function getForModel(Model $target, bool $includeInternal = true)
    {
        $query = $this->model
            ->where('commentable_type', $target->getMorphClass())
            ->where('commentable_id', $target->getKey())
            ->with($this->withRelations);

        if (!$includeInternal) {
            $query->where('is_internal', false);
        }

        return $this->mapItems($query->orderBy('created_at', 'asc')->paginate(50));
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'commentable_type' => $item->commentable_type,
            'commentable_id' => $item->commentable_id,
            'user_id' => $item->user_id,
            'user' => $item->relationLoaded('user') && $item->user
                ? ['id' => $item->user->id, 'name' => $item->user->name]
                : null,
            'body' => $item->body,
            'is_internal' => (bool) $item->is_internal,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
