<?php

namespace App\Http\Actions\Api\V1\Rdr;

use App\Repositories\Rdr\RdrCaseRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResolveRdrAction
{
    public function __construct(private RdrCaseRepo $rdrRepo) {}

    public function handle(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'rdr_resolution' => ['required', 'in:accepted,declined'],
        ]);

        $record = $this->rdrRepo->recordResolution($id, $data['rdr_resolution']);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
