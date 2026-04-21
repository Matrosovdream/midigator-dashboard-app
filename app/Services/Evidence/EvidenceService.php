<?php

namespace App\Services\Evidence;

use App\Models\User;
use App\Repositories\Evidence\EvidenceFileRepo;
use App\Repositories\Order\OrderRepo;
use App\Services\Cases\CaseRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class EvidenceService
{
    private const ALLOWED_TYPES = ['chargeback', 'prevention', 'rdr', 'order'];

    public function __construct(
        private EvidenceFileRepo $evidenceRepo,
        private CaseRegistry $registry,
        private OrderRepo $orderRepo,
    ) {}

    public function listFor(string $caseType, int $id): array
    {
        $model = $this->resolveModel($caseType, $id);
        return $this->evidenceRepo->getForModel($model);
    }

    public function upload(string $caseType, int $id, User $user, UploadedFile $file): array
    {
        $model = $this->resolveModel($caseType, $id);

        $path = $file->store("evidence/{$user->tenant_id}/{$caseType}/{$id}", 'local');

        $record = $this->evidenceRepo->addToModel(
            $model,
            (int) $user->tenant_id,
            $user->id,
            $file->getClientOriginalName(),
            $path,
            $file->getClientMimeType(),
            (int) $file->getSize(),
        );

        unset($record['Model']);
        return $record;
    }

    public function delete(int $evidenceId): bool
    {
        $record = $this->evidenceRepo->getByID($evidenceId);
        if (!$record) {
            return false;
        }

        Storage::disk('local')->delete($record['Model']->path);
        return $this->evidenceRepo->delete($evidenceId);
    }

    private function resolveModel(string $caseType, int $id): Model
    {
        if (!in_array($caseType, self::ALLOWED_TYPES, true)) {
            throw new RuntimeException("Evidence not supported for case type: $caseType");
        }

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
