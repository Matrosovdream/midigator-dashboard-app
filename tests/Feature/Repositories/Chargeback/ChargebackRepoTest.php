<?php

namespace Tests\Feature\Repositories\Chargeback;

use App\Repositories\Chargeback\ChargebackRepo;
use Tests\Feature\Repositories\RepoTestCase;

class ChargebackRepoTest extends RepoTestCase
{
    private function basePayload(int $tenantId, string $guid = 'cb-guid-1'): array
    {
        return [
            'tenant_id' => $tenantId,
            'chargeback_guid' => $guid,
            'event_guid' => 'evt-'.$guid,
            'mid' => 'MID-001',
            'amount' => 12345,
            'currency' => 'USD',
        ];
    }

    public function test_create_and_get_by_guid(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(ChargebackRepo::class);

        $cb = $repo->create($this->basePayload($tenant->id));

        $fetched = $repo->getByGuid('cb-guid-1');
        $this->assertSame($cb['id'], $fetched['id']);
        $this->assertSame(12345, $fetched['amount']);
        $this->assertSame('new', $fetched['stage']);
        $this->assertSame('pending', $fetched['result']);
    }

    public function test_change_stage_assign_and_hide(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(ChargebackRepo::class);

        $cb = $repo->create($this->basePayload($tenant->id));

        $assigned = $repo->assign($cb['id'], $user->id);
        $this->assertSame($user->id, $assigned['assigned_to']);
        $this->assertSame($user->name, $assigned['assignee']['name']);

        $staged = $repo->changeStage($cb['id'], 'in_review');
        $this->assertSame('in_review', $staged['stage']);

        $hidden = $repo->setHidden($cb['id'], true);
        $this->assertTrue($hidden['is_hidden']);
    }

    public function test_get_for_tenant_filters_by_tenant_and_stage(): void
    {
        $t1 = $this->makeTenant();
        $t2 = $this->makeTenant();
        $repo = app(ChargebackRepo::class);

        $repo->create($this->basePayload($t1->id, 'g1') + ['stage' => 'new']);
        $repo->create($this->basePayload($t1->id, 'g2') + ['stage' => 'resolved']);
        $repo->create($this->basePayload($t2->id, 'g3') + ['stage' => 'new']);

        $this->assertCount(2, $repo->getForTenant($t1->id)['items']);
        $this->assertCount(1, $repo->getForTenant($t1->id, ['stage' => 'new'])['items']);
        $this->assertCount(1, $repo->getForTenant($t2->id)['items']);
    }
}
