<?php

namespace App\Http\Controllers\Api\V1\Comment;

use App\Http\Actions\Api\V1\Comment\DeleteCommentAction;
use App\Http\Actions\Api\V1\Comment\ListCommentsAction;
use App\Http\Actions\Api\V1\Comment\StoreCommentAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, string $type, int $id, ListCommentsAction $action): JsonResponse
    {
        return $action->handle($request, $type, $id);
    }

    public function store(Request $request, string $type, int $id, StoreCommentAction $action): JsonResponse
    {
        return $action->handle($request, $type, $id);
    }

    public function destroy(int $commentId, DeleteCommentAction $action): JsonResponse
    {
        return $action->handle($commentId);
    }
}
