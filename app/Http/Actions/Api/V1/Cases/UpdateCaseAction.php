<?php

namespace App\Http\Actions\Api\V1\Cases;

use App\Services\Cases\CaseRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateCaseAction
{
    public function __construct(private CaseRegistry $registry) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $repo = $this->registry->repoFor($caseType);
        $record = $repo->update($id, $data);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
