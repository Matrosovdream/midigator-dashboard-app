<?php

namespace App\Http\Actions\Api\V1\Cases;

use App\Services\Cases\CaseHideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HideCaseAction
{
    public function __construct(private CaseHideService $hideService) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        $data = $request->validate([
            'is_hidden' => ['required', 'boolean'],
        ]);

        $record = $this->hideService->setHidden($caseType, $id, (bool) $data['is_hidden']);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }

    public function bulk(Request $request, string $caseType): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'is_hidden' => ['required', 'boolean'],
        ]);

        $count = $this->hideService->bulkSetHidden($caseType, $data['ids'], (bool) $data['is_hidden']);
        return response()->json(['updated' => $count]);
    }
}
