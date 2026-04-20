<?php

namespace App\Http\Actions\Api\V1\Comment;

use App\Services\Comments\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreCommentAction
{
    public function __construct(private CommentService $comments) {}

    public function handle(Request $request, string $caseType, int $id): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $isInternal = (bool) ($data['is_internal'] ?? false);
        if ($isInternal && !$user->hasRight('comments.internal')) {
            abort(403, 'Not allowed to post internal comments.');
        }

        $record = $this->comments->add($caseType, $id, $user, $data['body'], $isInternal);
        unset($record['Model']);

        return response()->json($record, 201);
    }
}
