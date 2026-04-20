<?php

namespace App\Repositories\User;

use App\Models\UserRoleRight;
use App\Repositories\AbstractRepo;

class UserRoleRightRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new UserRoleRight();
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'user_role_id' => $item->user_role_id,
            'role_code' => $item->role_code,
            'role_id' => $item->role_id,
            'Model' => $item,
        ];
    }
}
