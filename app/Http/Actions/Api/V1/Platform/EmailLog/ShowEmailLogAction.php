<?php

namespace App\Http\Actions\Api\V1\Platform\EmailLog;

use App\Services\Platform\EmailLogService;
use Illuminate\Http\JsonResponse;

class ShowEmailLogAction
{
    public function __construct(private EmailLogService $emailLogs) {}

    public function handle(int $id): JsonResponse
    {
        $log = $this->emailLogs->show($id);
        if (!$log) {
            abort(404, 'Email log not found');
        }
        return response()->json(['email' => $log]);
    }
}
