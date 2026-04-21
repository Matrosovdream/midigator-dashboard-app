<?php

namespace App\Services\DummyData\Importers;

use App\Repositories\Rdr\RdrCaseRepo;
use App\Services\DummyData\DummyDataLoader;
use App\Services\DummyData\TenantAssignmentPicker;

class RdrCaseImporter
{
    public function __construct(
        private DummyDataLoader $loader,
        private RdrCaseRepo $rdrRepo,
    ) {}

    public function import(TenantAssignmentPicker $picker): int
    {
        $count = 0;
        $count += $this->importFile($picker, 'rdr-new.json', false);
        $count += $this->importFile($picker, 'rdr-match.json', true);
        return $count;
    }

    private function importFile(TenantAssignmentPicker $picker, string $file, bool $withMatch): int
    {
        $count = 0;
        foreach ($this->loader->load($file) as $p) {
            $guid = $p['rdr_guid'] ?? null;
            if (!$guid || $this->rdrRepo->getByGuid($guid)) {
                continue;
            }

            $tenantId = $picker->nextTenantId();
            $data = [
                'tenant_id' => $tenantId,
                'rdr_guid' => $guid,
                'event_guid' => $p['event_guid'] ?? $guid,
                'rdr_case_number' => $p['rdr_case_number'] ?? null,
                'rdr_date' => $p['rdr_date'] ?? null,
                'rdr_resolution' => isset($p['rdr_resolution']) ? strtolower($p['rdr_resolution']) : null,
                'arn' => $p['arn'] ?? null,
                'auth_code' => $p['auth_code'] ?? null,
                'amount' => (int) ($p['amount'] ?? 0),
                'currency' => $p['currency'] ?? 'USD',
                'card_first_6' => $p['card_first_6'] ?? null,
                'card_last_4' => $p['card_last_4'] ?? null,
                'merchant_descriptor' => $p['merchant_descriptor'] ?? null,
                'prevention_type' => $p['prevention_type'] ?? null,
                'transaction_date' => $p['transaction_date'] ?? null,
                'assigned_to' => $picker->randomAssigneeId($tenantId),
            ];

            if ($withMatch) {
                $data['order_guid'] = $p['order_guid'] ?? null;
                $data['order_id'] = $p['order_id'] ?? null;
                $data['crm_account_guid'] = $p['crm_account_guid'] ?? null;
            }

            $this->rdrRepo->create($data);
            $count++;
        }
        return $count;
    }
}
