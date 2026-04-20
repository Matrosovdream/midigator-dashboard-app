<?php

namespace App\Http\Actions\Api\V1\Auth;

use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeAction
{
    public function __construct(private AuthService $auth) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->auth->presentUser($request->user()),
        ]);
    }
}
