<?php

namespace App\Http\Actions\Api\V1\Manager;

use App\Services\Cases\CaseAssignmentService;
use App\Services\Cases\CaseRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignCaseAction
{
    private const KIND_MAP = [
        'chargebacks' => CaseRegistry::CHARGEBACK,
        'preventions' => CaseRegistry::PREVENTION,
        'rdr' => CaseRegistry::RDR,
    ];

    public function __construct(private CaseAssignmentService $assignments) {}

    public function handle(Request $request, string $kind, int $id): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $caseType = self::KIND_MAP[$kind] ?? null;
        if ($caseType === null) {
            return response()->json(['message' => 'Unknown case kind'], 422);
        }

        $updated = $this->assignments->assign($caseType, $id, $data['user_id'] ?? null, $request->user()->id);

        return response()->json(['item' => $updated]);
    }
}
