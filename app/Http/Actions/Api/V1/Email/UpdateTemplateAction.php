<?php

namespace App\Http\Actions\Api\V1\Email;

use App\Services\Emails\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $record = $this->templates->update($id, $data);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
