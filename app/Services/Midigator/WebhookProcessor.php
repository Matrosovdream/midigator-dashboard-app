<?php

namespace App\Services\Midigator;

use App\Events\ChargebackReceived;
use App\Events\PreventionAlertReceived;
use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Order\OrderValidationRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;
use App\Repositories\Webhook\WebhookLogRepo;
use RuntimeException;
use Throwable;

class WebhookProcessor
{
    public function __construct(
        private WebhookLogRepo $webhookLogRepo,
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionRepo,
        private OrderValidationRepo $orderValidationRepo,
        private RdrCaseRepo $rdrRepo,
    ) {}

    public function process(int $webhookLogId, int $tenantId, string $eventType, array $payload): void
    {
        try {
            match ($eventType) {
                'chargeback.new' => $this->handleChargebackNew($tenantId, $payload),
                'chargeback.match' => $this->handleChargebackMatch($payload),
                'chargeback.result' => $this->handleChargebackResult($payload),
                'chargeback.dnf' => $this->handleChargebackDnf($payload),
                'prevention.new' => $this->handlePreventionNew($tenantId, $payload),
                'prevention.match' => $this->handlePreventionMatch($payload),
                'order_validation.new' => $this->handleOrderValidationNew($tenantId, $payload),
                'order_validation.match' => $this->handleOrderValidationMatch($payload),
                'rdr.new' => $this->handleRdrNew($tenantId, $payload),
                'rdr.match' => $this->handleRdrMatch($payload),
                'registration.new' => null,
                default => throw new RuntimeException("Unknown event type: $eventType"),
            };

            $this->webhookLogRepo->markProcessed($webhookLogId);
        } catch (Throwable $e) {
            $this->webhookLogRepo->markFailed($webhookLogId, $e->getMessage());
            throw $e;
        }
    }

    private function handleChargebackNew(int $tenantId, array $p): void
    {
        $guid = $this->guid($p, 'chargeback_guid');
        $existing = $this->chargebackRepo->getByGuid($guid);
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
        ];

        $record = $existing
            ? $this->chargebackRepo->update($existing['id'], $data)
            : $this->chargebackRepo->create($data);

        if (!$existing && $record) {
            ChargebackReceived::dispatch($tenantId, (int) $record['id']);
        }
    }

    private function handleChargebackMatch(array $p): void
    {
        $record = $this->chargebackRepo->getByGuid($this->guid($p, 'chargeback_guid'));
        if (!$record) return;

        $this->chargebackRepo->update($record['id'], array_filter([
            'order_guid' => $p['order_guid'] ?? null,
            'order_id' => $p['order_id'] ?? null,
            'crm_account_guid' => $p['crm_account_guid'] ?? null,
        ], fn ($v) => $v !== null));
    }

    private function handleChargebackResult(array $p): void
    {
        $record = $this->chargebackRepo->getByGuid($this->guid($p, 'chargeback_guid'));
        if (!$record) return;

        $result = $p['result'] ?? 'pending';
        $this->chargebackRepo->update($record['id'], ['result' => $result]);
    }

    private function handleChargebackDnf(array $p): void
    {
        $record = $this->chargebackRepo->getByGuid($this->guid($p, 'chargeback_guid'));
        if (!$record) return;

        $this->chargebackRepo->update($record['id'], [
            'dnf_reason' => $p['dnf_reason'] ?? null,
            'dnf_timestamp' => $p['dnf_timestamp'] ?? now(),
        ]);
    }

    private function handlePreventionNew(int $tenantId, array $p): void
    {
        $guid = $this->guid($p, 'prevention_guid');
        $existing = $this->preventionRepo->getByGuid($guid);
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
        ];

        $record = $existing
            ? $this->preventionRepo->update($existing['id'], $data)
            : $this->preventionRepo->create($data);

        if (!$existing && $record) {
            PreventionAlertReceived::dispatch($tenantId, (int) $record['id']);
        }
    }

    private function handlePreventionMatch(array $p): void
    {
        $record = $this->preventionRepo->getByGuid($this->guid($p, 'prevention_guid'));
        if (!$record) return;

        $this->preventionRepo->update($record['id'], array_filter([
            'order_guid' => $p['order_guid'] ?? null,
            'order_id' => $p['order_id'] ?? null,
            'crm_account_guid' => $p['crm_account_guid'] ?? null,
        ], fn ($v) => $v !== null));
    }

    private function handleOrderValidationNew(int $tenantId, array $p): void
    {
        $guid = $this->guid($p, 'order_validation_guid');
        $existing = $this->orderValidationRepo->getByGuid($guid);
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
        ];

        $existing
            ? $this->orderValidationRepo->update($existing['id'], $data)
            : $this->orderValidationRepo->create($data);
    }

    private function handleOrderValidationMatch(array $p): void
    {
        $record = $this->orderValidationRepo->getByGuid($this->guid($p, 'order_validation_guid'));
        if (!$record) return;

        $this->orderValidationRepo->update($record['id'], array_filter([
            'order_guid' => $p['order_guid'] ?? null,
            'order_id' => $p['order_id'] ?? null,
            'crm_account_guid' => $p['crm_account_guid'] ?? null,
        ], fn ($v) => $v !== null));
    }

    private function handleRdrNew(int $tenantId, array $p): void
    {
        $guid = $this->guid($p, 'rdr_guid');
        $existing = $this->rdrRepo->getByGuid($guid);
        $data = [
            'tenant_id' => $tenantId,
            'rdr_guid' => $guid,
            'event_guid' => $p['event_guid'] ?? $guid,
            'rdr_case_number' => $p['rdr_case_number'] ?? null,
            'rdr_date' => $p['rdr_date'] ?? null,
            'rdr_resolution' => $p['rdr_resolution'] ?? null,
            'arn' => $p['arn'] ?? null,
            'auth_code' => $p['auth_code'] ?? null,
            'amount' => (int) ($p['amount'] ?? 0),
            'currency' => $p['currency'] ?? 'USD',
            'card_first_6' => $p['card_first_6'] ?? null,
            'card_last_4' => $p['card_last_4'] ?? null,
            'merchant_descriptor' => $p['merchant_descriptor'] ?? null,
            'prevention_type' => $p['prevention_type'] ?? null,
            'transaction_date' => $p['transaction_date'] ?? null,
        ];

        $existing
            ? $this->rdrRepo->update($existing['id'], $data)
            : $this->rdrRepo->create($data);
    }

    private function handleRdrMatch(array $p): void
    {
        $record = $this->rdrRepo->getByGuid($this->guid($p, 'rdr_guid'));
        if (!$record) return;

        $this->rdrRepo->update($record['id'], array_filter([
            'order_guid' => $p['order_guid'] ?? null,
            'order_id' => $p['order_id'] ?? null,
            'crm_account_guid' => $p['crm_account_guid'] ?? null,
        ], fn ($v) => $v !== null));
    }

    private function guid(array $p, string $key): string
    {
        $guid = $p[$key] ?? null;
        if (!is_string($guid) || $guid === '') {
            throw new RuntimeException("Missing $key in webhook payload.");
        }
        return $guid;
    }
}
