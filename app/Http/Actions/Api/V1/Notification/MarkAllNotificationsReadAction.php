<?php

namespace App\Http\Actions\Api\V1\Notification;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkAllNotificationsReadAction
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(Request $request): JsonResponse
    {
        $count = $this->notifications->markAllRead($request->user()->id);
        return response()->json(['updated' => $count]);
    }
}
