<?php

namespace App\Http\Actions\Api\V1\Platform\Setting;

use App\Services\Platform\SettingsService;
use Illuminate\Http\JsonResponse;

class ListSettingsAction
{
    public function __construct(private SettingsService $settings) {}

    public function handle(): JsonResponse
    {
        return response()->json($this->settings->list());
    }
}
