<?php

namespace App\Repositories\User;

use App\Models\UserRole;
use App\Repositories\AbstractRepo;

class UserRoleRepo extends AbstractRepo
{
    protected $withRelations = ['rights'];

    public function __construct()
    {
        $this->model = new UserRole();
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'is_editable' => $item->is_editable,
            'is_active' => $item->is_active,
            'rights' => $item->relationLoaded('rights') ? $item->rights->toArray() : [],
            'Model' => $item,
        ];
    }
}
