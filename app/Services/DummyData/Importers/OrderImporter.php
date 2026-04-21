<?php

namespace App\Services\DummyData\Importers;

use App\Repositories\Order\OrderRepo;
use App\Services\DummyData\DummyDataLoader;
use App\Services\DummyData\TenantAssignmentPicker;
use Illuminate\Support\Str;

class OrderImporter
{
    public function __construct(
        private DummyDataLoader $loader,
        private OrderRepo $orderRepo,
    ) {}

    public function import(TenantAssignmentPicker $picker): int
    {
        $count = 0;
        foreach ($this->loader->load('orders.json') as $order) {
            $tenantId = $picker->nextTenantId();
            $existing = $this->orderRepo->getByOrderId($tenantId, (string) ($order['order_id'] ?? ''));
            if ($existing) {
                continue;
            }

            $this->orderRepo->create($this->map($order, $tenantId));
            $count++;
        }
        return $count;
    }

    private function map(array $p, int $tenantId): array
    {
        return [
            'tenant_id' => $tenantId,
            'order_guid' => $p['order_guid'] ?? 'ord_'.Str::random(32),
            'order_id' => $p['order_id'] ?? null,
            'mid' => $p['mid'] ?? null,
            'order_date' => $p['order_date'] ?? null,
            'order_amount' => (int) ($p['order_amount'] ?? 0),
            'currency' => $p['currency'] ?? 'USD',
            'card_brand' => $p['card_brand'] ?? null,
            'card_first_6' => $p['card_first_6'] ?? null,
            'card_last_4' => $p['card_last_4'] ?? null,
            'card_exp_month' => $p['card_exp_month'] ?? null,
            'card_exp_year' => $p['card_exp_year'] ?? null,
            'avs' => $p['avs'] ?? null,
            'cvv' => $p['cvv'] ?? null,
            'processor_auth_code' => $p['processor_auth_code'] ?? null,
            'processor_transaction_id' => $p['processor_transaction_id'] ?? null,
            'email' => $p['email'] ?? null,
            'phone' => $p['phone'] ?? null,
            'ip_address' => $p['ip_address'] ?? null,
            'billing_first_name' => $p['billing_first_name'] ?? null,
            'billing_last_name' => $p['billing_last_name'] ?? null,
            'billing_address' => $this->extractBillingAddress($p),
            'refunded' => (bool) ($p['refunded'] ?? false),
            'refunded_amount' => isset($p['refunded_amount']) ? (int) $p['refunded_amount'] : null,
            'subscription_cycle' => $p['subscription_cycle'] ?? null,
            'subscription_parent_id' => $p['subscription_parent_id'] ?? null,
            'marketing_source' => $p['marketing_source'] ?? null,
            'sub_marketing_sources' => $p['sub_marketing_sources'] ?? null,
            'items' => $p['items'] ?? null,
            'evidence' => $p['evidence'] ?? null,
        ];
    }

    private function extractBillingAddress(array $p): ?array
    {
        $fields = [
            'street_address_1' => $p['billing_street_address_1'] ?? null,
            'street_address_2' => $p['billing_street_address_2'] ?? null,
            'city' => $p['billing_city'] ?? null,
            'state' => $p['billing_state'] ?? null,
            'postcode' => $p['billing_postcode'] ?? null,
            'country' => $p['billing_country'] ?? null,
        ];

        return array_filter($fields, fn ($v) => $v !== null) ?: null;
    }
}
