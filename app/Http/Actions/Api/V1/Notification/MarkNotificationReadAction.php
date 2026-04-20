<?php

namespace App\Http\Actions\Api\V1\Notification;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;

class MarkNotificationReadAction
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(string $id): JsonResponse
    {
        $record = $this->notifications->markRead($id);
        abort_if($record === null, 404);

        unset($record['Model']);
        return response()->json($record);
    }
}
