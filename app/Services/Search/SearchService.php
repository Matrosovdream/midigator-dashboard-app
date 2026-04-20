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

        return [
            'chargebacks' => $this->search($this->chargebackRepo->getModel(), $tenantId, $like, [
                'case_number', 'chargeback_guid', 'arn', 'card_last_4', 'order_id',
            ], $limit),
            'preventions' => $this->search($this->preventionRepo->getModel(), $tenantId, $like, [
                'prevention_case_number', 'prevention_guid', 'arn', 'card_last_4', 'order_id',
            ], $limit),
            'orders' => $this->search($this->orderRepo->getModel(), $tenantId, $like, [
                'order_id', 'order_guid', 'card_last_4', 'email',
            ], $limit),
            'order_validations' => $this->search($this->orderValidationRepo->getModel(), $tenantId, $like, [
                'order_validation_guid', 'arn', 'card_last_4', 'order_id',
            ], $limit),
            'rdr' => $this->search($this->rdrRepo->getModel(), $tenantId, $like, [
                'rdr_case_number', 'rdr_guid', 'arn', 'card_last_4', 'order_id',
            ], $limit),
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
