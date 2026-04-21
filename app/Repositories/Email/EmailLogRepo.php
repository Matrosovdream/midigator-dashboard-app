<?php

namespace App\Repositories\Email;

use App\Models\EmailLog;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;

class EmailLogRepo extends AbstractRepo
{
    protected $withRelations = ['user', 'template'];

    public function getAllCrossTenant(array $filter = [], int $perPage = 25): array
    {
        $search = $filter['search'] ?? null;
        unset($filter['search']);

        $query = $this->model->with(array_merge($this->withRelations, ['tenant']));
        $query = $this->applyFilter($query, $filter);

        if (!empty($search)) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('to_email', 'LIKE', $like)
                    ->orWhere('subject', 'LIKE', $like);
            });
        }

        $query->orderBy('created_at', 'desc');

        return $this->mapItems($query->paginate($perPage));
    }

    public function getByIDWithBody(int $id): ?array
    {
        $item = $this->model
            ->with(array_merge($this->withRelations, ['tenant']))
            ->find($id);
        if (!$item) {
            return null;
        }
        $mapped = $this->mapItem($item);
        $mapped['body'] = $item->body;
        return $mapped;
    }

    public function __construct()
    {
        $this->model = new EmailLog();
    }

    public function logForModel(Model $target, int $tenantId, int $userId, array $data): array
    {
        return $this->create(array_merge($data, [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'emailable_type' => $target->getMorphClass(),
            'emailable_id' => $target->getKey(),
        ]));
    }

    public function markStatus(int $id, string $status, ?\DateTimeInterface $sentAt = null): ?array
    {
        $payload = ['status' => $status];
        if ($sentAt) {
            $payload['sent_at'] = $sentAt;
        }
        return $this->update($id, $payload);
    }

    public function getForModel(Model $target)
    {
        $items = $this->model
            ->where('emailable_type', $target->getMorphClass())
            ->where('emailable_id', $target->getKey())
            ->with($this->withRelations)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

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
            'tenant' => $item->relationLoaded('tenant') && $item->tenant
                ? ['id' => $item->tenant->id, 'name' => $item->tenant->name]
                : null,
            'user_id' => $item->user_id,
            'user' => $item->relationLoaded('user') && $item->user
                ? ['id' => $item->user->id, 'name' => $item->user->name]
                : null,
            'emailable_type' => $item->emailable_type,
            'emailable_id' => $item->emailable_id,
            'to_email' => $item->to_email,
            'subject' => $item->subject,
            'status' => $item->status,
            'sent_at' => $item->sent_at,
            'template_id' => $item->template_id,
            'template' => $item->relationLoaded('template') && $item->template
                ? ['id' => $item->template->id, 'name' => $item->template->name]
                : null,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
