<?php

namespace App\Http\Actions\Api\V1\Email;

use App\Services\Emails\EmailSendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListEmailLogsAction
{
    public function __construct(private EmailSendService $sender) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json(
            $this->sender->listForTenant($request->user()->tenant_id, (int) $request->integer('per_page', 50)),
        );
    }
}
