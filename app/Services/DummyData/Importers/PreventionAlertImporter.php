<?php

namespace App\Services\DummyData\Importers;

use App\Repositories\Prevention\PreventionAlertRepo;
use App\Services\DummyData\DummyDataLoader;
use App\Services\DummyData\TenantAssignmentPicker;

class PreventionAlertImporter
{
    public function __construct(
        private DummyDataLoader $loader,
        private PreventionAlertRepo $preventionRepo,
    ) {}

    public function import(TenantAssignmentPicker $picker): int
    {
        $count = 0;
        $count += $this->importFile($picker, 'prevention-new.json', false);
        $count += $this->importFile($picker, 'prevention-match.json', true);
        return $count;
    }

    private function importFile(TenantAssignmentPicker $picker, string $file, bool $withMatch): int
    {
        $count = 0;
        foreach ($this->loader->load($file) as $p) {
            $guid = $p['prevention_guid'] ?? null;
            if (!$guid || $this->preventionRepo->getByGuid($guid)) {
                continue;
            }

            $tenantId = $picker->nextTenantId();
            $data = [
                'tenant_id' => $tenantId,
                'prevention_guid' => $guid,
                'event_guid' => $p['event_guid'] ?? $guid,
                'prevention_case_number' => $p['prevention_case_number'] ?? null,
                'prevention_type' => $p['prevention_type'] ?? 'ethoca',
                'arn' => $p['arn'] ?? null,
                'mid' => $p['mid'] ?? null,
                'amount' => (int) ($p['amount'] ?? 0),
                'currency' => $p['currency'] ?? 'USD',
                'card_first_6' => $p['card_first_6'] ?? null,
                'card_last_4' => $p['card_last_4'] ?? null,
                'merchant_descriptor' => $p['merchant_descriptor'] ?? null,
                'prevention_timestamp' => $p['prevention_timestamp'] ?? null,
                'transaction_timestamp' => $p['transaction_timestamp'] ?? null,
                'assigned_to' => $picker->randomAssigneeId($tenantId),
            ];

            if ($withMatch) {
                $data['order_guid'] = $p['order_guid'] ?? null;
                $data['order_id'] = $p['order_id'] ?? null;
                $data['crm_account_guid'] = $p['crm_account_guid'] ?? null;
            }

            $this->preventionRepo->create($data);
            $count++;
        }
        return $count;
    }
}
