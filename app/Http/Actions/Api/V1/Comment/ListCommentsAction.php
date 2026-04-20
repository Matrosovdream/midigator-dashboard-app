<?php

namespace App\Http\Actions\Api\V1\Comment;

use App\Services\Comments\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListCommentsAction
{
    public function __construct(private CommentService $comments) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        return response()->json(
            $this->comments->listFor($caseType, $id, $request->user()),
        );
    }
}
