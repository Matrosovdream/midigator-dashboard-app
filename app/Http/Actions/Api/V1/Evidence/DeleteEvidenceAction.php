<?php

namespace App\Http\Actions\Api\V1\Evidence;

use App\Services\Evidence\EvidenceService;
use Illuminate\Http\JsonResponse;

class DeleteEvidenceAction
{
    public function __construct(private EvidenceService $evidence) {}

    public function handle(int $evidenceId): JsonResponse
    {
        $deleted = $this->evidence->delete($evidenceId);
        abort_unless($deleted, 404);

        return response()->json(['deleted' => true]);
    }
}
