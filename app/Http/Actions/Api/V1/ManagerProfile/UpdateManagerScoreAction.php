<?php

namespace App\Http\Actions\Api\V1\ManagerProfile;

use App\Repositories\User\ManagerProfileRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateManagerScoreAction
{
    public function __construct(private ManagerProfileRepo $profileRepo) {}

    public function handle(Request $request, int $userId): JsonResponse
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
        ]);

        $record = $this->profileRepo->upsertForUser($userId, $data);
        unset($record['Model']);

        return response()->json($record);
    }
}
