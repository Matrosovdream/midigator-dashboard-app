<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Actions\Api\V1\User\ListUsersAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, ListUsersAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
