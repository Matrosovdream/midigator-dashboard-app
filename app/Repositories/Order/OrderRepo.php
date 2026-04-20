<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Repositories\AbstractRepo;

class OrderRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new Order();
    }

    public function getByGuid(string $guid)
    {
        $item = $this->model->where('order_guid', $guid)->first();
        return $this->mapItem($item);
    }

    public function getByOrderId(int $tenantId, string $orderId)
    {
        $item = $this->model
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->first();

        return $this->mapItem($item);
    }

    public function markRefunded(int $id, ?int $refundedAmount = null): ?array
    {
        return $this->update($id, [
            'refunded' => true,
            'refunded_amount' => $refundedAmount,
        ]);
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'tenant_id' => $item->tenant_id,
            'order_guid' => $item->order_guid,
            'order_id' => $item->order_id,
            'mid' => $item->mid,
            'order_amount' => (int) $item->order_amount,
            'currency' => $item->currency,
            'card_brand' => $item->card_brand,
            'email' => $item->email,
            'refunded' => (bool) $item->refunded,
            'refunded_amount' => $item->refunded_amount !== null ? (int) $item->refunded_amount : null,
            'is_hidden' => (bool) $item->is_hidden,
            'order_date' => $item->order_date,
            'Model' => $item,
        ];
    }
}
