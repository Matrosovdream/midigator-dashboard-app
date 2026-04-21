<?php

namespace App\Http\Actions\Api\V1\Platform\Setting;

use App\Services\Platform\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpsertSettingsAction
{
    public function __construct(private SettingsService $settings) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:255'],
            'settings.*.value' => ['nullable'],
            'settings.*.type' => ['required', 'in:string,integer,boolean,json'],
            'settings.*.group' => ['required', 'string', 'max:255'],
        ]);

        return response()->json($this->settings->bulkUpsert($data['settings']));
    }
}
