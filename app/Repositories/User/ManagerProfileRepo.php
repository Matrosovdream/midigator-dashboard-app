<?php

namespace App\Repositories\User;

use App\Models\ManagerProfile;
use App\Repositories\AbstractRepo;

class ManagerProfileRepo extends AbstractRepo
{
    protected $withRelations = ['user'];

    public function __construct()
    {
        $this->model = new ManagerProfile();
    }

    public function upsertForUser(int $userId, array $data): array
    {
        $profile = $this->model->updateOrCreate(['user_id' => $userId], $data);

        return $this->mapItem($profile->fresh($this->withRelations));
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'score' => $item->score !== null ? (float) $item->score : null,
            'notes' => $item->notes,
            'assigned_mids' => $item->assigned_mids ?? [],
            'user' => $item->relationLoaded('user') && $item->user
                ? [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
                    'email' => $item->user->email,
                ]
                : null,
            'Model' => $item,
        ];
    }
}
