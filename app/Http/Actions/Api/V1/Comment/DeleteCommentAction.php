<?php

namespace App\Http\Actions\Api\V1\Comment;

use App\Services\Comments\CommentService;
use Illuminate\Http\JsonResponse;

class DeleteCommentAction
{
    public function __construct(private CommentService $comments) {}

    public function handle(int $commentId): JsonResponse
    {
        $deleted = $this->comments->delete($commentId);
        abort_unless($deleted, 404);

        return response()->json(['deleted' => true]);
    }
}
