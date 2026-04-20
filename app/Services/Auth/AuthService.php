<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\User\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepo $userRepo) {}

    public function attempt(string $email, string $password, Request $request): ?User
    {
        $record = $this->userRepo->getByEmail($email);
        if (!$record) {
            return null;
        }

        /** @var User $user */
        $user = $record['Model'];

        if (!$user->is_active) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->userRepo->touchLastLogin($user->id);

        return $user->fresh(['roles']);
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function presentUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        $record = $this->userRepo->getByID($user->id);
        if (!$record) {
            return null;
        }

        $record['rights'] = $user->allRights();
        unset($record['Model']);

        return $record;
    }
}
