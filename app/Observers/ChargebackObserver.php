<?php

namespace App\Observers;

use App\Models\Chargeback;
use App\Services\Activity\ActivityLogService;

class ChargebackObserver
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function created(Chargeback $chargeback): void
    {
        $this->activityLog->log($chargeback->tenant_id, auth()->id(), 'chargeback.created', $chargeback, [
            'chargeback_guid' => $chargeback->chargeback_guid,
        ]);
    }

    public function updated(Chargeback $chargeback): void
    {
        $this->activityLog->log($chargeback->tenant_id, auth()->id(), 'chargeback.updated', $chargeback, [
            'changes' => $chargeback->getChanges(),
        ]);
    }
}
