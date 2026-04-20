<?php

namespace App\Services\Midigator;

use App\Models\Tenant;
use Illuminate\Http\Client\Response;
use RuntimeException;

class EventSubscriptionService
{
    public function __construct(private MidigatorClient $client) {}

    public function subscribe(Tenant $tenant, string $eventType, string $webhookUrl, string $basicUsername, string $basicPassword): Response
    {
        return $this->client->post($tenant, '/events/v1/subscribe', [
            'event_type' => $eventType,
            'url' => $webhookUrl,
            'basic_auth' => [
                'username' => $basicUsername,
                'password' => $basicPassword,
            ],
        ]);
    }

    public function listSubscriptions(Tenant $tenant): array
    {
        $response = $this->client->get($tenant, '/events/v1/subscribe');
        $this->ensureOk($response, 'list subscriptions');
        return (array) $response->json();
    }

    public function getSubscription(Tenant $tenant, string $eventGuid): array
    {
        $response = $this->client->get($tenant, "/events/v1/subscribe/$eventGuid");
        $this->ensureOk($response, 'get subscription');
        return (array) $response->json();
    }

    public function updateSubscription(Tenant $tenant, string $eventGuid, array $data): Response
    {
        return $this->client->put($tenant, "/events/v1/subscribe/$eventGuid", $data);
    }

    public function deleteSubscription(Tenant $tenant, string $eventGuid): Response
    {
        return $this->client->delete($tenant, "/events/v1/subscribe/$eventGuid");
    }

    public function ping(Tenant $tenant, string $eventType): array
    {
        $response = $this->client->get($tenant, "/events/v1/ping/$eventType");
        $this->ensureOk($response, 'ping');
        return (array) $response->json();
    }

    public function subscribeAll(Tenant $tenant, string $webhookUrl, string $basicUsername, string $basicPassword): array
    {
        $results = [];
        foreach ((array) config('midigator.event_types', []) as $eventType) {
            if ($eventType === 'registration.new') {
                continue;
            }
            $results[$eventType] = $this->subscribe($tenant, $eventType, $webhookUrl, $basicUsername, $basicPassword)->successful();
        }
        return $results;
    }

    private function ensureOk(Response $response, string $op): void
    {
        if (!$response->successful()) {
            throw new RuntimeException("Midigator $op failed: ".$response->status().' '.$response->body());
        }
    }
}
