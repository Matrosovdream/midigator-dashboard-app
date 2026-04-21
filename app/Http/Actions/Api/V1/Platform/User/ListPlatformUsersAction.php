<?php

namespace App\Http\Actions\Api\V1\Platform\User;

use App\Services\Platform\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListPlatformUsersAction
{
    public function __construct(private UserService $users) {}

    public function handle(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'tenant_id' => $request->input('tenant_id'),
            'is_active' => $request->input('is_active'),
            'is_platform_admin' => $request->input('is_platform_admin'),
        ];

        $perPage = (int) $request->input('per_page', 20);

        return response()->json($this->users->list($filters, $perPage));
    }
}
