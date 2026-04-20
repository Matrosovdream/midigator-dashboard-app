<?php

namespace Tests\Feature\Repositories\Order;

use App\Repositories\Order\OrderRepo;
use Tests\Feature\Repositories\RepoTestCase;

class OrderRepoTest extends RepoTestCase
{
    public function test_create_with_json_columns_roundtrips(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(OrderRepo::class);

        $order = $repo->create([
            'tenant_id' => $tenant->id,
            'order_guid' => 'o-1',
            'order_id' => 'ORD-001',
            'order_amount' => 10000,
            'currency' => 'USD',
            'billing_address' => ['city' => 'NYC', 'country' => 'US'],
            'items' => [['sku' => 'X', 'price' => 100, 'quantity' => 1]],
        ]);

        $fetched = $repo->getByID($order['id']);
        $this->assertSame(['city' => 'NYC', 'country' => 'US'], $fetched['Model']->billing_address);
        $this->assertSame('X', $fetched['Model']->items[0]['sku']);
    }

    public function test_get_by_guid_and_order_id(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(OrderRepo::class);

        $order = $repo->create([
            'tenant_id' => $tenant->id,
            'order_guid' => 'o-2',
            'order_id' => 'ORD-002',
            'order_amount' => 1,
        ]);

        $this->assertSame($order['id'], $repo->getByGuid('o-2')['id']);
        $this->assertSame($order['id'], $repo->getByOrderId($tenant->id, 'ORD-002')['id']);
        $this->assertNull($repo->getByOrderId($tenant->id, 'missing'));
    }

    public function test_mark_refunded(): void
    {
        $repo = app(OrderRepo::class);
        $order = $repo->create([
            'tenant_id' => $this->makeTenant()->id,
            'order_guid' => 'o-3',
            'order_amount' => 5000,
        ]);

        $updated = $repo->markRefunded($order['id'], 5000);
        $this->assertTrue($updated['refunded']);
        $this->assertSame(5000, $updated['refunded_amount']);
    }
}
