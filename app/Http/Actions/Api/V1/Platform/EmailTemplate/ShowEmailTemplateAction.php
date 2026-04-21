<?php

namespace App\Http\Actions\Api\V1\Platform\EmailTemplate;

use App\Services\Platform\EmailTemplateService;
use Illuminate\Http\JsonResponse;

class ShowEmailTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(int $id): JsonResponse
    {
        $template = $this->templates->show($id);
        if (!$template) {
            abort(404, 'Global template not found');
        }
        return response()->json(['template' => $template]);
    }
}
