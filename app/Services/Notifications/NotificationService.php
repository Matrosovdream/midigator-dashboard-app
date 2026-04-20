<?php

namespace App\Services\Notifications;

use App\Events\UserNotified;
use App\Repositories\Notification\NotificationRepo;
use App\Repositories\Notification\NotificationSettingRepo;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    public function __construct(
        private NotificationRepo $notificationRepo,
        private NotificationSettingRepo $settingsRepo,
    ) {}

    public function notify(int $tenantId, int $userId, string $type, string $title, string $body, ?Model $notifiable = null): ?array
    {
        if (!$this->userWantsChannel($userId, $type, 'in_app')) {
            return null;
        }

        $record = $this->notificationRepo->notify($tenantId, $userId, $type, $title, $body, $notifiable);
        UserNotified::dispatch($userId, (string) $record['id'], $type, $title, $body);

        return $record;
    }

    public function listUnread(int $userId, int $perPage = 30): ?array
    {
        return $this->notificationRepo->getUnreadForUser($userId, $perPage);
    }

    public function markRead(string $id): ?array
    {
        return $this->notificationRepo->markRead($id);
    }

    public function markAllRead(int $userId): int
    {
        return $this->notificationRepo->markAllReadForUser($userId);
    }

    public function userSettings(int $userId): array
    {
        return $this->settingsRepo->getOrDefaultForUser($userId);
    }

    public function updateUserSettings(int $userId, array $data): array
    {
        return $this->settingsRepo->updateForUser($userId, $data);
    }

    public function userWantsChannel(int $userId, string $type, string $channel): bool
    {
        $record = $this->settingsRepo->getOrDefaultForUser($userId);
        $channelField = (string) ($record['channel'] ?? 'both');

        $wantsThisChannel = match ($channel) {
            'email' => in_array($channelField, ['email', 'both'], true),
            'in_app' => in_array($channelField, ['in_app', 'both'], true),
            default => true,
        };

        if (!$wantsThisChannel) {
            return false;
        }

        $typeToggle = match ($type) {
            'chargeback.new' => $record['chargeback_new'] ?? true,
            'chargeback.result' => $record['chargeback_result'] ?? true,
            'prevention.new' => $record['prevention_new'] ?? true,
            default => true,
        };

        return (bool) $typeToggle;
    }
}
