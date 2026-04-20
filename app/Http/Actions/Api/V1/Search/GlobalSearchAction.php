<?php

namespace App\Http\Actions\Api\V1\Search;

use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchAction
{
    public function __construct(private SearchService $search) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json(
            $this->search->searchAll(
                $request->user()->tenant_id,
                $data['q'],
                (int) ($data['limit'] ?? 10),
            ),
        );
    }
}
