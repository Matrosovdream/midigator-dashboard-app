<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Actions\Api\V1\Auth\LoginAction;
use App\Http\Actions\Api\V1\Auth\LogoutAction;
use App\Http\Actions\Api\V1\Auth\MeAction;
use App\Http\Actions\Api\V1\Auth\PinLoginAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request, LoginAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function loginPin(Request $request, PinLoginAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function me(Request $request, MeAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
