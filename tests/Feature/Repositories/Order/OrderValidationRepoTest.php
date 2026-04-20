<?php

namespace Tests\Feature\Repositories\Order;

use App\Repositories\Order\OrderValidationRepo;
use Tests\Feature\Repositories\RepoTestCase;

class OrderValidationRepoTest extends RepoTestCase
{
    public function test_create_and_get_by_guid(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(OrderValidationRepo::class);

        $created = $repo->create([
            'tenant_id' => $tenant->id,
            'order_validation_guid' => 'ov-1',
            'event_guid' => 'evt-ov-1',
            'amount' => 999,
        ]);

        $fetched = $repo->getByGuid('ov-1');
        $this->assertSame($created['id'], $fetched['id']);
        $this->assertSame('new', $fetched['stage']);
    }

    public function test_get_for_tenant(): void
    {
        $tenant = $this->makeTenant();
        $other = $this->makeTenant();
        $repo = app(OrderValidationRepo::class);

        $repo->create(['tenant_id' => $tenant->id, 'order_validation_guid' => 'a', 'event_guid' => 'e1', 'amount' => 1]);
        $repo->create(['tenant_id' => $tenant->id, 'order_validation_guid' => 'b', 'event_guid' => 'e2', 'amount' => 2]);
        $repo->create(['tenant_id' => $other->id, 'order_validation_guid' => 'c', 'event_guid' => 'e3', 'amount' => 3]);

        $this->assertCount(2, $repo->getForTenant($tenant->id)['items']);
    }
}
