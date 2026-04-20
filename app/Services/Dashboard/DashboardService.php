<?php

namespace App\Services\Dashboard;

class DashboardService
{
    public function summaryForTenant(?int $tenantId): array
    {
        return [
            'tenant_id' => $tenantId,
            'open_chargebacks' => 0,
            'active_alerts' => 0,
            'open_rdr_cases' => 0,
            'pending_orders' => 0,
        ];
    }
}
