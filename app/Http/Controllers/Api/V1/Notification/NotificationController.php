<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Actions\Api\V1\Notification\GetNotificationSettingsAction;
use App\Http\Actions\Api\V1\Notification\ListNotificationsAction;
use App\Http\Actions\Api\V1\Notification\MarkAllNotificationsReadAction;
use App\Http\Actions\Api\V1\Notification\MarkNotificationReadAction;
use App\Http\Actions\Api\V1\Notification\UpdateNotificationSettingsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, ListNotificationsAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function markRead(string $id, MarkNotificationReadAction $action): JsonResponse
    {
        return $action->handle($id);
    }

    public function markAllRead(Request $request, MarkAllNotificationsReadAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function settings(Request $request, GetNotificationSettingsAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function updateSettings(Request $request, UpdateNotificationSettingsAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
