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

    public function createAndSubmit(Tenant $tenant, array $data): array
    {
        $record = $this->orderRepo->create(array_merge($data, [
            'tenant_id' => $tenant->id,
            'submission_status' => 'pending',
        ]));

        $response = $this->send($tenant, $record);
        $record = $this->recordOutcome($record['id'], $response);

        return [
            'record' => $record,
            'midigator_status' => $response->status(),
            'midigator_body' => $response->json(),
        ];
    }

    public function submit(Tenant $tenant, int $orderId): Response
    {
        $record = $this->orderRepo->getByID($orderId);
        if (!$record) {
            throw new RuntimeException("Order $orderId not found.");
        }

        $response = $this->send($tenant, $record);
        $this->recordOutcome($orderId, $response);

        return $response;
    }

    private function send(Tenant $tenant, array $record): Response
    {
        return $this->client->post($tenant, '/orders/v2/order', $this->buildBody($record));
    }

    private function recordOutcome(int $orderId, Response $response): ?array
    {
        return $this->orderRepo->update($orderId, $response->successful()
            ? ['submission_status' => 'submitted', 'submission_error' => null, 'submitted_at' => now()]
            : ['submission_status' => 'failed', 'submission_error' => $this->extractError($response)]
        );
    }

    private function extractError(Response $response): string
    {
        $body = $response->json();
        if (is_array($body) && isset($body['message'])) {
            return (string) $body['message'];
        }
        if (is_array($body) && isset($body['error'])) {
            return is_string($body['error']) ? $body['error'] : json_encode($body['error']);
        }
        return substr((string) $response->body(), 0, 500);
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
