<?php

namespace App\Http\Actions\Api\V1\Platform\Impersonation;

use App\Services\Auth\AuthService;
use App\Services\Platform\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StopImpersonationAction
{
    public function __construct(
        private ImpersonationService $impersonation,
        private AuthService $auth,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $original = $this->impersonation->stop($request);
        if (!$original) {
            return response()->json(['message' => 'Not currently impersonating.'], 422);
        }

        return response()->json([
            'user' => $this->auth->presentUser($original),
            'impersonator' => null,
        ]);
    }
}
