<?php

namespace App\Http\Actions\Api\V1\Platform\Setting;

use App\Services\Platform\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteSettingAction
{
    public function __construct(private SettingsService $settings) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
        ]);

        $ok = $this->settings->delete($data['key']);
        if (!$ok) {
            abort(404, 'Setting not found');
        }
        return response()->json(['ok' => true]);
    }
}
