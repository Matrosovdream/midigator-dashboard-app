<?php

namespace App\Http\Actions\Api\V1\Platform\EmailTemplate;

use App\Services\Platform\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StoreEmailTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        try {
            $template = $this->templates->create($data);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['template' => $template], 201);
    }
}
