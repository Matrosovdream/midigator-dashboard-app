<?php

namespace App\Http\Actions\Api\V1\Platform\EmailTemplate;

use App\Services\Platform\EmailTemplateService;
use Illuminate\Http\JsonResponse;

class DeleteEmailTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(int $id): JsonResponse
    {
        $ok = $this->templates->delete($id);
        if (!$ok) {
            abort(404, 'Global template not found');
        }
        return response()->json(['ok' => true]);
    }
}
