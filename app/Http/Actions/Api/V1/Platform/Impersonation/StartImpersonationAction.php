<?php

namespace App\Http\Actions\Api\V1\Platform\Impersonation;

use App\Services\Auth\AuthService;
use App\Services\Platform\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StartImpersonationAction
{
    public function __construct(
        private ImpersonationService $impersonation,
        private AuthService $auth,
    ) {}

    public function handle(Request $request, int $userId): JsonResponse
    {
        try {
            $target = $this->impersonation->start($request, $request->user(), $userId);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'user' => $this->auth->presentUser($target),
            'impersonator' => $this->impersonation->impersonator($request),
        ]);
    }
}
