<?php

namespace App\Http\Actions\Api\V1\Notification;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateNotificationSettingsAction
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel' => ['nullable', 'in:email,in_app,both'],
            'chargeback_new' => ['nullable', 'boolean'],
            'chargeback_result' => ['nullable', 'boolean'],
            'prevention_new' => ['nullable', 'boolean'],
            'daily_digest' => ['nullable', 'boolean'],
            'weekly_report' => ['nullable', 'boolean'],
        ]);

        $record = $this->notifications->updateUserSettings($request->user()->id, $data);
        unset($record['Model']);

        return response()->json($record);
    }
}
