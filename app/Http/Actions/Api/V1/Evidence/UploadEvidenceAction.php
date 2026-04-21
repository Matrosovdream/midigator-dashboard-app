<?php

namespace App\Http\Actions\Api\V1\Evidence;

use App\Services\Evidence\EvidenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadEvidenceAction
{
    public function __construct(private EvidenceService $evidence) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $record = $this->evidence->upload($caseType, $id, $request->user(), $data['file']);

        return response()->json($record, 201);
    }
}
