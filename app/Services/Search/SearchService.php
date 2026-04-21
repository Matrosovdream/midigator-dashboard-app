<?php

namespace App\Services\Search;

use App\Repositories\Chargeback\ChargebackRepo;
use App\Repositories\Order\OrderRepo;
use App\Repositories\Order\OrderValidationRepo;
use App\Repositories\Prevention\PreventionAlertRepo;
use App\Repositories\Rdr\RdrCaseRepo;

class SearchService
{
    public function __construct(
        private ChargebackRepo $chargebackRepo,
        private PreventionAlertRepo $preventionRepo,
        private OrderRepo $orderRepo,
        private OrderValidationRepo $orderValidationRepo,
        private RdrCaseRepo $rdrRepo,
    ) {}

    public function searchAll(int $tenantId, string $query, int $limit = 10): array
    {
        $like = '%'.$query.'%';

        $chargebacks = $this->search($this->chargebackRepo->getModel(), $tenantId, $like, [
            'case_number', 'chargeback_guid', 'arn', 'card_last_4', 'order_id',
        ], $limit);
        $preventions = $this->search($this->preventionRepo->getModel(), $tenantId, $like, [
            'prevention_case_number', 'prevention_guid', 'arn', 'card_last_4', 'order_id',
        ], $limit);
        $orders = $this->search($this->orderRepo->getModel(), $tenantId, $like, [
            'order_id', 'order_guid', 'card_last_4', 'email',
        ], $limit);
        $orderValidations = $this->search($this->orderValidationRepo->getModel(), $tenantId, $like, [
            'order_validation_guid', 'arn', 'card_last_4', 'order_id',
        ], $limit);
        $rdrs = $this->search($this->rdrRepo->getModel(), $tenantId, $like, [
            'rdr_case_number', 'rdr_guid', 'arn', 'card_last_4', 'order_id',
        ], $limit);

        $items = array_merge(
            array_map(fn ($r) => $this->format('chargeback', $r, $r['case_number'] ?? "#{$r['id']}", (string) ($r['arn'] ?? '')), $chargebacks),
            array_map(fn ($r) => $this->format('prevention', $r, $r['prevention_case_number'] ?? "#{$r['id']}", (string) ($r['provider'] ?? '')), $preventions),
            array_map(fn ($r) => $this->format('rdr', $r, $r['rdr_case_number'] ?? "#{$r['id']}", (string) ($r['arn'] ?? '')), $rdrs),
            array_map(fn ($r) => $this->format('order', $r, $r['order_id'] ?? "#{$r['id']}", (string) ($r['email'] ?? '')), $orders),
            array_map(fn ($r) => $this->format('order_validation', $r, $r['order_validation_guid'] ?? "#{$r['id']}", (string) ($r['arn'] ?? '')), $orderValidations),
        );

        return [
            'items' => $items,
            'groups' => [
                'chargebacks' => $chargebacks,
                'preventions' => $preventions,
                'orders' => $orders,
                'order_validations' => $orderValidations,
                'rdr' => $rdrs,
            ],
        ];
    }

    private function format(string $type, array $row, string $label, string $meta): array
    {
        return [
            'type' => $type,
            'id' => (int) $row['id'],
            'label' => $label,
            'meta' => $meta,
        ];
    }

    private function search($model, int $tenantId, string $like, array $columns, int $limit): array
    {
        return $model->where('tenant_id', $tenantId)
            ->where(function ($q) use ($like, $columns) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'LIKE', $like);
                }
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
