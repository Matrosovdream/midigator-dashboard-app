<?php

namespace App\Services\DummyData\Importers;

use App\Repositories\Chargeback\ChargebackRepo;
use App\Services\DummyData\DummyDataLoader;
use App\Services\DummyData\TenantAssignmentPicker;

class ChargebackImporter
{
    public function __construct(
        private DummyDataLoader $loader,
        private ChargebackRepo $chargebackRepo,
    ) {}

    public function import(TenantAssignmentPicker $picker): int
    {
        $count = 0;

        $count += $this->importNew($picker, 'chargeback-new.json', false);
        $count += $this->importNew($picker, 'chargeback-match.json', true);
        $this->applyUpdates('chargeback-result.json', fn ($p) => ['result' => $p['result'] ?? 'pending']);
        $this->applyUpdates('chargeback-dnf.json', fn ($p) => [
            'dnf_reason' => $p['dnf_reason'] ?? null,
            'dnf_timestamp' => $p['dnf_timestamp'] ?? null,
        ]);

        return $count;
    }

    private function importNew(TenantAssignmentPicker $picker, string $file, bool $withMatch): int
    {
        $count = 0;
        foreach ($this->loader->load($file) as $p) {
            $guid = $p['chargeback_guid'] ?? null;
            if (!$guid || $this->chargebackRepo->getByGuid($guid)) {
                continue;
            }

            $tenantId = $picker->nextTenantId();
            $data = [
                'tenant_id' => $tenantId,
                'chargeback_guid' => $guid,
                'event_guid' => $p['event_guid'] ?? $guid,
                'case_number' => $p['case_number'] ?? null,
                'arn' => $p['arn'] ?? null,
                'mid' => $p['mid'] ?? '',
                'amount' => (int) ($p['amount'] ?? 0),
                'currency' => $p['currency'] ?? 'USD',
                'card_brand' => $p['card_brand'] ?? null,
                'card_first_6' => $p['card_first_6'] ?? null,
                'card_last_4' => $p['card_last_4'] ?? null,
                'reason_code' => $p['reason_code'] ?? null,
                'reason_description' => $p['reason_description'] ?? null,
                'chargeback_date' => $p['chargeback_date'] ?? null,
                'date_received' => $p['date_received'] ?? null,
                'due_date' => $p['due_date'] ?? null,
                'processor_transaction_id' => $p['processor_transaction_id'] ?? null,
                'processor_transaction_date' => $p['processor_transaction_date'] ?? null,
                'auth_code' => $p['auth_code'] ?? null,
                'alternate_transaction_id' => $p['alternate_transaction_id'] ?? null,
                'assigned_to' => $picker->randomAssigneeId($tenantId),
            ];

            if ($withMatch) {
                $data['order_guid'] = $p['order_guid'] ?? null;
                $data['order_id'] = $p['order_id'] ?? null;
                $data['crm_account_guid'] = $p['crm_account_guid'] ?? null;
            }

            $this->chargebackRepo->create($data);
            $count++;
        }
        return $count;
    }

    private function applyUpdates(string $file, callable $mapper): void
    {
        foreach ($this->loader->load($file) as $p) {
            $guid = $p['chargeback_guid'] ?? null;
            if (!$guid) {
                continue;
            }
            $existing = $this->chargebackRepo->getByGuid($guid);
            if (!$existing) {
                continue;
            }
            $this->chargebackRepo->update($existing['id'], array_filter($mapper($p), fn ($v) => $v !== null));
        }
    }
}
