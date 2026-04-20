<?php

namespace App\Repositories\Email;

use App\Models\EmailLog;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;

class EmailLogRepo extends AbstractRepo
{
    protected $withRelations = ['user', 'template'];

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
            'user_id' => $item->user_id,
            'emailable_type' => $item->emailable_type,
            'emailable_id' => $item->emailable_id,
            'to_email' => $item->to_email,
            'subject' => $item->subject,
            'status' => $item->status,
            'sent_at' => $item->sent_at,
            'template_id' => $item->template_id,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
