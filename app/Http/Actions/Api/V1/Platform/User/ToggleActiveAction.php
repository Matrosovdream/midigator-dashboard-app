<?php

namespace App\Http\Actions\Api\V1\Platform\User;

use App\Services\Platform\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ToggleActiveAction
{
    public function __construct(private UserService $users) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->users->toggleActive($id, (int) $request->user()->id);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        if (!$user) {
            abort(404, 'User not found');
        }
        unset($user['Model']);
        return response()->json(['user' => $user]);
    }
}
