<?php

namespace App\Http\Actions\Api\V1\Auth;

use App\Services\Auth\AuthService;
use App\Services\Platform\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeAction
{
    public function __construct(
        private AuthService $auth,
        private ImpersonationService $impersonation,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->auth->presentUser($request->user()),
            'impersonator' => $this->impersonation->impersonator($request),
        ]);
    }
}
