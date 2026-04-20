<?php

namespace App\Http\Actions\Api\V1\Auth;

use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutAction
{
    public function __construct(private AuthService $auth) {}

    public function handle(Request $request): JsonResponse
    {
        $this->auth->logout($request);
        return response()->json(['ok' => true]);
    }
}
