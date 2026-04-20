<?php

namespace App\Http\Actions\Api\V1\Auth;

use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function __construct(private AuthService $auth) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->auth->attempt($data['email'], $data['password'], $request);
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return response()->json([
            'user' => $this->auth->presentUser($user),
        ]);
    }
}
