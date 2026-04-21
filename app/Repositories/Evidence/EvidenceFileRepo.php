<?php

namespace App\Repositories\Evidence;

use App\Models\EvidenceFile;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EvidenceFileRepo extends AbstractRepo
{
    protected $withRelations = ['uploader'];

    public function __construct()
    {
        $this->model = new EvidenceFile();
    }

    public function addToModel(Model $target, int $tenantId, int $userId, string $name, string $path, ?string $mime, int $size): array
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'evidenceable_type' => $target->getMorphClass(),
            'evidenceable_id' => $target->getKey(),
            'uploaded_by' => $userId,
            'name' => $name,
            'path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $size,
        ]);
    }

    public function getForModel(Model $target)
    {
        $items = $this->model
            ->where('evidenceable_type', $target->getMorphClass())
            ->where('evidenceable_id', $target->getKey())
            ->with($this->withRelations)
            ->orderBy('created_at', 'desc')
            ->paginate(100);

        return $this->mapItems($items);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'evidenceable_type' => $item->evidenceable_type,
            'evidenceable_id' => $item->evidenceable_id,
            'uploaded_by' => $item->uploaded_by,
            'uploader' => $item->relationLoaded('uploader') && $item->uploader
                ? ['id' => $item->uploader->id, 'name' => $item->uploader->name]
                : null,
            'name' => $item->name,
            'url' => Storage::disk('local')->url($item->path),
            'mime_type' => $item->mime_type,
            'size_bytes' => (int) $item->size_bytes,
            'uploaded_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
