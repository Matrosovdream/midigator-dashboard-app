<?php

namespace App\Services\Platform;

use App\Models\User;
use App\Repositories\User\UserRepo;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ImpersonationService
{
    private const SESSION_KEY = 'impersonator_id';

    public function __construct(
        private UserRepo $userRepo,
        private ActivityLogService $activityLog,
    ) {}

    public function start(Request $request, User $actor, int $targetUserId): User
    {
        if (!$actor->isPlatformAdmin()) {
            throw new RuntimeException('Only platform admins can impersonate.');
        }

        if ($request->session()->has(self::SESSION_KEY)) {
            throw new RuntimeException('Already impersonating. Stop the current session first.');
        }

        if ($actor->id === $targetUserId) {
            throw new RuntimeException('Cannot impersonate yourself.');
        }

        $record = $this->userRepo->getByID($targetUserId);
        if (!$record) {
            throw new RuntimeException('Target user not found.');
        }

        /** @var User $target */
        $target = $record['Model'];

        if ($target->is_platform_admin) {
            throw new RuntimeException('Cannot impersonate another platform admin.');
        }

        if (!$target->is_active) {
            throw new RuntimeException('Cannot impersonate an inactive user.');
        }

        $request->session()->put(self::SESSION_KEY, $actor->id);

        Auth::login($target);
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, $actor->id);

        $this->activityLog->log(
            (int) $target->tenant_id,
            $actor->id,
            'impersonation.start',
            $target,
            ['target_user_id' => $target->id, 'target_email' => $target->email],
        );

        return $target->fresh(['roles']);
    }

    public function stop(Request $request): ?User
    {
        $impersonatorId = $request->session()->get(self::SESSION_KEY);
        if (!$impersonatorId) {
            return null;
        }

        $record = $this->userRepo->getByID((int) $impersonatorId);
        if (!$record) {
            $request->session()->forget(self::SESSION_KEY);
            return null;
        }

        /** @var User $impersonator */
        $impersonator = $record['Model'];

        $current = $request->user();
        $targetTenantId = $current?->tenant_id;
        $targetUserId = $current?->id;

        Auth::login($impersonator);
        $request->session()->regenerate();
        $request->session()->forget(self::SESSION_KEY);

        if ($targetTenantId !== null && $targetUserId !== null) {
            $this->activityLog->log(
                (int) $targetTenantId,
                $impersonator->id,
                'impersonation.stop',
                null,
                ['target_user_id' => $targetUserId],
            );
        }

        return $impersonator->fresh(['roles']);
    }

    public function impersonator(Request $request): ?array
    {
        $id = $request->session()->get(self::SESSION_KEY);
        if (!$id) {
            return null;
        }

        $record = $this->userRepo->getByID((int) $id);
        if (!$record) {
            return null;
        }

        return [
            'id' => $record['id'],
            'name' => $record['name'],
            'email' => $record['email'],
        ];
    }
}
