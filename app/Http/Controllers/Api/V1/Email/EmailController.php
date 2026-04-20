<?php

namespace App\Http\Controllers\Api\V1\Email;

use App\Http\Actions\Api\V1\Email\ListEmailLogsAction;
use App\Http\Actions\Api\V1\Email\SendEmailAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function send(Request $request, SendEmailAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function logs(Request $request, ListEmailLogsAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
