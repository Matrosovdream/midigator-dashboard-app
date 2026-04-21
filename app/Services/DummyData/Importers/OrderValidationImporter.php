<?php

namespace App\Services\DummyData\Importers;

use App\Repositories\Order\OrderValidationRepo;
use App\Services\DummyData\DummyDataLoader;
use App\Services\DummyData\TenantAssignmentPicker;

class OrderValidationImporter
{
    public function __construct(
        private DummyDataLoader $loader,
        private OrderValidationRepo $orderValidationRepo,
    ) {}

    public function import(TenantAssignmentPicker $picker): int
    {
        $count = 0;
        $count += $this->importFile($picker, 'order-validation-new.json', false);
        $count += $this->importFile($picker, 'order-validation-match.json', true);
        return $count;
    }

    private function importFile(TenantAssignmentPicker $picker, string $file, bool $withMatch): int
    {
        $count = 0;
        foreach ($this->loader->load($file) as $p) {
            $guid = $p['order_validation_guid'] ?? null;
            if (!$guid || $this->orderValidationRepo->getByGuid($guid)) {
                continue;
            }

            $tenantId = $picker->nextTenantId();
            $data = [
                'tenant_id' => $tenantId,
                'order_validation_guid' => $guid,
                'event_guid' => $p['event_guid'] ?? $guid,
                'order_validation_type' => $p['order_validation_type'] ?? null,
                'order_validation_timestamp' => $p['order_validation_timestamp'] ?? null,
                'transaction_timestamp' => $p['transaction_timestamp'] ?? null,
                'amount' => (int) ($p['amount'] ?? 0),
                'currency' => $p['currency'] ?? 'USD',
                'card_first_6' => $p['card_first_6'] ?? null,
                'card_last_4' => $p['card_last_4'] ?? null,
                'arn' => $p['arn'] ?? null,
                'auth_code' => $p['auth_code'] ?? null,
                'card_brand' => $p['card_brand'] ?? null,
                'mid' => $p['mid'] ?? null,
                'assigned_to' => $picker->randomAssigneeId($tenantId),
            ];

            if ($withMatch) {
                $data['order_guid'] = $p['order_guid'] ?? null;
                $data['order_id'] = $p['order_id'] ?? null;
                $data['crm_account_guid'] = $p['crm_account_guid'] ?? null;
            }

            $this->orderValidationRepo->create($data);
            $count++;
        }
        return $count;
    }
}
