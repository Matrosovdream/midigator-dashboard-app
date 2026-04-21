<?php

namespace App\Http\Controllers\Api\V1\Evidence;

use App\Http\Actions\Api\V1\Evidence\DeleteEvidenceAction;
use App\Http\Actions\Api\V1\Evidence\ListEvidenceAction;
use App\Http\Actions\Api\V1\Evidence\UploadEvidenceAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvidenceController extends Controller
{
    public function index(string $type, int $id, ListEvidenceAction $action): JsonResponse
    {
        return $action->handle($type, $id);
    }

    public function store(Request $request, string $type, int $id, UploadEvidenceAction $action): JsonResponse
    {
        return $action->handle($request, $type, $id);
    }

    public function destroy(int $evidenceId, DeleteEvidenceAction $action): JsonResponse
    {
        return $action->handle($evidenceId);
    }
}
