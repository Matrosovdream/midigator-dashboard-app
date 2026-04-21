<?php

namespace App\Http\Actions\Api\V1\Platform\EmailTemplate;

use App\Services\Platform\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateEmailTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
            'variables' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $template = $this->templates->update($id, $data);
        if (!$template) {
            abort(404, 'Global template not found');
        }

        return response()->json(['template' => $template]);
    }
}
