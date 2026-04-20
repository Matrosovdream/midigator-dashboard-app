<?php

namespace App\Http\Actions\Api\V1\Notification;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetNotificationSettingsAction
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(Request $request): JsonResponse
    {
        $record = $this->notifications->userSettings($request->user()->id);
        unset($record['Model']);
        return response()->json($record);
    }
}
