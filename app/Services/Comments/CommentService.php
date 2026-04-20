<?php

namespace App\Services\Comments;

use App\Models\User;
use App\Repositories\Comment\CommentRepo;
use App\Services\Cases\CaseRegistry;
use App\Repositories\Order\OrderRepo;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CommentService
{
    public function __construct(
        private CommentRepo $commentRepo,
        private CaseRegistry $registry,
        private OrderRepo $orderRepo,
    ) {}

    public function listFor(string $caseType, int $id, User $user)
    {
        $model = $this->resolveModel($caseType, $id);
        $includeInternal = $user->hasRight('activity_log.view_all') || $user->isPlatformAdmin();

        return $this->commentRepo->getForModel($model, $includeInternal);
    }

    public function add(string $caseType, int $id, User $user, string $body, bool $isInternal): array
    {
        $model = $this->resolveModel($caseType, $id);

        return $this->commentRepo->addToModel($model, $user->id, $body, $isInternal);
    }

    public function delete(int $commentId): bool
    {
        return $this->commentRepo->delete($commentId);
    }

    private function resolveModel(string $caseType, int $id): Model
    {
        if ($caseType === 'order') {
            $record = $this->orderRepo->getByID($id);
        } else {
            $record = $this->registry->repoFor($caseType)->getByID($id);
        }

        if (!$record) {
            throw new RuntimeException("$caseType#$id not found.");
        }

        return $record['Model'];
    }
}
