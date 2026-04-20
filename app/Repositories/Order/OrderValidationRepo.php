<?php

namespace App\Repositories\Order;

use App\Models\OrderValidation;
use App\Repositories\AbstractRepo;

class OrderValidationRepo extends AbstractRepo
{
    protected $withRelations = ['assignee'];

    public function __construct()
    {
        $this->model = new OrderValidation();
    }

    public function getByGuid(string $guid)
    {
        $item = $this->model->where('order_validation_guid', $guid)->with($this->withRelations)->first();
        return $this->mapItem($item);
    }

    public function getForTenant(int $tenantId, array $filter = [], $paginate = 25, array $sorting = [])
    {
        return $this->getAll(array_merge(['tenant_id' => $tenantId], $filter), $paginate, $sorting);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'order_validation_guid' => $item->order_validation_guid,
            'order_validation_type' => $item->order_validation_type,
            'amount' => (int) $item->amount,
            'currency' => $item->currency,
            'mid' => $item->mid,
            'stage' => $item->stage,
            'is_hidden' => (bool) $item->is_hidden,
            'assigned_to' => $item->assigned_to,
            'created_at' => $item->created_at,
            'Model' => $item,
        ];
    }
}
