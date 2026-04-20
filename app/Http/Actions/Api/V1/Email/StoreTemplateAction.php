<?php

namespace App\Http\Actions\Api\V1\Email;

use App\Services\Emails\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $record = $this->templates->create($request->user()->tenant_id, $data);
        unset($record['Model']);

        return response()->json($record, 201);
    }
}
