<?php

namespace App\Http\Actions\Api\V1\Cases;

use App\Services\Cases\CaseQueryService;
use Illuminate\Http\JsonResponse;

class ShowCaseAction
{
    public function __construct(private CaseQueryService $query) {}

    public function handle(string $caseType, int $id): JsonResponse
    {
        $record = $this->query->get($caseType, $id);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
