<?php

namespace App\Repositories\Notification;

use App\Models\Notification;
use App\Repositories\AbstractRepo;
use Illuminate\Database\Eloquent\Model;

class NotificationRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Notification();
    }

    public function notify(int $tenantId, int $userId, string $type, string $title, string $body, ?Model $notifiable = null): array
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'notifiable_type' => $notifiable?->getMorphClass(),
            'notifiable_id' => $notifiable?->getKey(),
        ]);
    }

    public function getUnreadForUser(int $userId, $paginate = 30)
    {
        $items = $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->paginate($paginate);

        return $this->mapItems($items);
    }

    public function markRead(string $id): ?array
    {
        $item = $this->model->find($id);
        if (!$item) {
            return null;
        }
        $item->update(['read_at' => now()]);
        return $this->mapItem($item->fresh());
    }

    public function markAllReadForUser(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'tenant_id' => $item->tenant_id,
            'type' => $item->type,
            'title' => $item->title,
            'body' => $item->body,
            'notifiable_type' => $item->notifiable_type,
            'notifiable_id' => $item->notifiable_id,
            'read_at' => $item->read_at,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
