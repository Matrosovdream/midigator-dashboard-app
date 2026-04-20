<?php

namespace App\Http\Actions\Api\V1\User;

use App\Services\Users\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListUsersAction
{
    public function __construct(private UserService $users) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json(
            $this->users->listForTenant($request->user()->tenant_id, (int) $request->input('per_page', 20)),
        );
    }
}
