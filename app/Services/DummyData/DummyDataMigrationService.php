<?php

namespace App\Services\DummyData;

use App\Services\DummyData\Importers\ChargebackImporter;
use App\Services\DummyData\Importers\OrderImporter;
use App\Services\DummyData\Importers\OrderValidationImporter;
use App\Services\DummyData\Importers\PreventionAlertImporter;
use App\Services\DummyData\Importers\RdrCaseImporter;

class DummyDataMigrationService
{
    public function __construct(
        private OrderImporter $orderImporter,
        private ChargebackImporter $chargebackImporter,
        private PreventionAlertImporter $preventionImporter,
        private RdrCaseImporter $rdrImporter,
        private OrderValidationImporter $orderValidationImporter,
    ) {}

    public function run(?string $tenantSlug = null): array
    {
        $picker = new TenantAssignmentPicker($tenantSlug);

        return [
            'orders' => $this->orderImporter->import($picker),
            'chargebacks' => $this->chargebackImporter->import($picker),
            'prevention_alerts' => $this->preventionImporter->import($picker),
            'rdr_cases' => $this->rdrImporter->import($picker),
            'order_validations' => $this->orderValidationImporter->import($picker),
        ];
    }
}
