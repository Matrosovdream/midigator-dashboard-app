<?php

namespace App\Services\Midigator;

use App\Models\Tenant;
use App\Repositories\Order\OrderRepo;
use Illuminate\Http\Client\Response;
use RuntimeException;

class OrderService
{
    public function __construct(
        private MidigatorClient $client,
        private OrderRepo $orderRepo,
    ) {}

    public function submit(Tenant $tenant, int $orderId): Response
    {
        $record = $this->orderRepo->getByID($orderId);
        if (!$record) {
            throw new RuntimeException("Order $orderId not found.");
        }

        $body = $this->buildBody($record);
        return $this->client->post($tenant, '/orders/v2/order', $body);
    }

    private function buildBody(array $record): array
    {
        return [
            'order_guid' => $record['order_guid'],
            'order_id' => $record['order_id'],
            'mid' => $record['mid'],
            'order_date' => optional($record['Model']->order_date)?->toIso8601String(),
            'order_amount' => (int) $record['order_amount'],
            'currency' => $record['currency'],
            'email' => $record['email'],
            'refunded' => (bool) $record['refunded'],
            'refunded_amount' => $record['refunded_amount'],
        ];
    }
}
