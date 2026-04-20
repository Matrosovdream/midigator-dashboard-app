<?php

namespace App\Observers;

use App\Models\PreventionAlert;
use App\Services\Activity\ActivityLogService;

class PreventionObserver
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function created(PreventionAlert $alert): void
    {
        $this->activityLog->log($alert->tenant_id, auth()->id(), 'prevention.created', $alert, [
            'prevention_guid' => $alert->prevention_guid,
        ]);
    }

    public function updated(PreventionAlert $alert): void
    {
        $this->activityLog->log($alert->tenant_id, auth()->id(), 'prevention.updated', $alert, [
            'changes' => $alert->getChanges(),
        ]);
    }
}
