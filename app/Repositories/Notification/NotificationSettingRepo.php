<?php

namespace App\Repositories\Notification;

use App\Models\NotificationSetting;
use App\Repositories\AbstractRepo;

class NotificationSettingRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new NotificationSetting();
    }

    public function getOrDefaultForUser(int $userId): array
    {
        $item = $this->model->firstOrCreate(['user_id' => $userId]);
        return $this->mapItem($item->fresh());
    }

    public function updateForUser(int $userId, array $data): array
    {
        $item = $this->model->updateOrCreate(['user_id' => $userId], $data);
        return $this->mapItem($item->fresh());
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'channel' => $item->channel,
            'chargeback_new' => (bool) $item->chargeback_new,
            'chargeback_result' => (bool) $item->chargeback_result,
            'prevention_new' => (bool) $item->prevention_new,
            'daily_digest' => (bool) $item->daily_digest,
            'weekly_report' => (bool) $item->weekly_report,
            'Model' => $item,
        ];
    }
}
