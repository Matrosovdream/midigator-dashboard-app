<?php

namespace App\Http\Actions\Api\V1\Platform\EmailTemplate;

use App\Services\Platform\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListEmailTemplatesAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        return response()->json($this->templates->listGlobal($perPage));
    }
}
