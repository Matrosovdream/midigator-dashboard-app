<?php

namespace App\Http\Actions\Api\V1\Email;

use App\Services\Emails\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListTemplatesAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json(
            $this->templates->list($request->user()->tenant_id, (int) $request->integer('per_page', 50)),
        );
    }
}
