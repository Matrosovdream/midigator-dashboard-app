<?php

namespace App\Http\Actions\Api\V1\Cases;

use App\Services\Cases\CaseStageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChangeCaseStageAction
{
    public function __construct(private CaseStageService $stageService) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        $data = $request->validate([
            'stage' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $result = $this->stageService->changeStage(
            $caseType,
            $id,
            $data['stage'],
            $request->user()->id,
            $data['notes'] ?? null,
        );

        unset($result['Model']);
        return response()->json($result);
    }
}
