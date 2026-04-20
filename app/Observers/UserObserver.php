<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function updated(User $user): void
    {
        if ($user->wasChanged('roles')) {
            $user->flushRightsCache();
        }
    }

    public function deleted(User $user): void
    {
        $user->flushRightsCache();
    }
}
