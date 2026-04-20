<?php

namespace App\Http\Actions\Api\V1\Cases;

use App\Services\Cases\CaseQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListCasesAction
{
    public function __construct(private CaseQueryService $query) {}

    public function handle(Request $request, string $caseType): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 25);
        $filter = (array) $request->input('filter', []);
        $sorting = (array) $request->input('sort', []);

        $result = $this->query->list($caseType, $request->user(), $filter, $perPage, $sorting);

        return response()->json($result);
    }
}
