<?php

namespace Tests\Feature\Repositories\Webhook;

use App\Repositories\Webhook\WebhookLogRepo;
use Tests\Feature\Repositories\RepoTestCase;

class WebhookLogRepoTest extends RepoTestCase
{
    public function test_create_with_payload_roundtrips(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(WebhookLogRepo::class);

        $entry = $repo->create([
            'tenant_id' => $tenant->id,
            'event_type' => 'chargeback.received',
            'event_guid' => 'evt-w-1',
            'payload' => ['foo' => 'bar', 'nested' => ['a' => 1]],
        ]);

        $fetched = $repo->getByEventGuid('evt-w-1');
        $this->assertSame($entry['id'], $fetched['id']);
        $this->assertSame('received', $fetched['status']);
        $this->assertSame(['foo' => 'bar', 'nested' => ['a' => 1]], $fetched['Model']->payload);
    }

    public function test_mark_processed_and_failed(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(WebhookLogRepo::class);

        $a = $repo->create(['tenant_id' => $tenant->id, 'event_type' => 't', 'event_guid' => 'a', 'payload' => []]);
        $b = $repo->create(['tenant_id' => $tenant->id, 'event_type' => 't', 'event_guid' => 'b', 'payload' => []]);

        $processed = $repo->markProcessed($a['id']);
        $failed = $repo->markFailed($b['id'], 'boom');

        $this->assertSame('processed', $processed['status']);
        $this->assertNotNull($processed['processed_at']);
        $this->assertSame('failed', $failed['status']);
        $this->assertSame('boom', $failed['error_message']);
    }

    public function test_get_for_tenant_with_filters(): void
    {
        $t1 = $this->makeTenant();
        $t2 = $this->makeTenant();
        $repo = app(WebhookLogRepo::class);

        $repo->create(['tenant_id' => $t1->id, 'event_type' => 'cb', 'event_guid' => 'g1', 'payload' => []]);
        $repo->create(['tenant_id' => $t1->id, 'event_type' => 'pa', 'event_guid' => 'g2', 'payload' => []]);
        $repo->create(['tenant_id' => $t2->id, 'event_type' => 'cb', 'event_guid' => 'g3', 'payload' => []]);

        $this->assertCount(2, $repo->getForTenant($t1->id)['items']);
        $this->assertCount(1, $repo->getForTenant($t1->id, ['event_type' => 'cb'])['items']);
    }
}
