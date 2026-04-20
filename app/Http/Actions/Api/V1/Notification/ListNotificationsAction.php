<?php

namespace App\Http\Actions\Api\V1\Notification;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListNotificationsAction
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(Request $request): JsonResponse
    {
        return response()->json(
            $this->notifications->listUnread(
                $request->user()->id,
                (int) $request->integer('per_page', 30),
            ),
        );
    }
}
