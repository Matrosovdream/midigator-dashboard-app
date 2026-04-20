<?php

namespace App\Http\Actions\Api\V1\Auth;

use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PinLoginAction
{
    public function __construct(private AuthService $auth) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:10'],
        ]);

        $user = $this->auth->attemptWithPin($data['pin'], $request);
        if (!$user) {
            throw ValidationException::withMessages([
                'pin' => __('auth.failed'),
            ]);
        }

        return response()->json([
            'user' => $this->auth->presentUser($user),
        ]);
    }
}
