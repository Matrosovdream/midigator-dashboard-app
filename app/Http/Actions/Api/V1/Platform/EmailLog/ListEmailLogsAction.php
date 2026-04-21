<?php

namespace App\Http\Actions\Api\V1\Platform\EmailLog;

use App\Services\Platform\EmailLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListEmailLogsAction
{
    public function __construct(private EmailLogService $emailLogs) {}

    public function handle(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'tenant_id' => $request->input('tenant_id'),
            'status' => $request->input('status'),
        ];
        $perPage = (int) $request->input('per_page', 25);
        return response()->json($this->emailLogs->list($filters, $perPage));
    }
}
