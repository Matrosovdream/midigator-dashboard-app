<?php

namespace App\Http\Actions\Api\V1\Cases;

use App\Services\Cases\CaseAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignCaseAction
{
    public function __construct(private CaseAssignmentService $assignmentService) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $record = $this->assignmentService->assign(
            $caseType,
            $id,
            $data['user_id'] ?? null,
            $request->user()->id,
        );

        unset($record['Model']);
        return response()->json($record);
    }
}
