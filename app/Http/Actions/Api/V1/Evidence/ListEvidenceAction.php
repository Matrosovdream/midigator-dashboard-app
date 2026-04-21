<?php

namespace App\Http\Actions\Api\V1\Evidence;

use App\Services\Evidence\EvidenceService;
use Illuminate\Http\JsonResponse;

class ListEvidenceAction
{
    public function __construct(private EvidenceService $evidence) {}

    public function handle(string $caseType, int $id): JsonResponse
    {
        return response()->json(
            $this->evidence->listFor($caseType, $id),
        );
    }
}
