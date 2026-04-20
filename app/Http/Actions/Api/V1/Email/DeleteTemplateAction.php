<?php

namespace App\Http\Actions\Api\V1\Email;

use App\Services\Emails\EmailTemplateService;
use Illuminate\Http\JsonResponse;

class DeleteTemplateAction
{
    public function __construct(private EmailTemplateService $templates) {}

    public function handle(int $id): JsonResponse
    {
        abort_unless($this->templates->delete($id), 404);
        return response()->json(['deleted' => true]);
    }
}
