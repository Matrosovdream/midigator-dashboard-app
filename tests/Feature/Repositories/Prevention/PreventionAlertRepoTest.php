<?php

namespace Tests\Feature\Repositories\Prevention;

use App\Repositories\Prevention\PreventionAlertRepo;
use Tests\Feature\Repositories\RepoTestCase;

class PreventionAlertRepoTest extends RepoTestCase
{
    private function basePayload(int $tenantId, string $guid = 'pa-1'): array
    {
        return [
            'tenant_id' => $tenantId,
            'prevention_guid' => $guid,
            'event_guid' => 'evt-'.$guid,
            'prevention_type' => 'ethoca',
            'amount' => 5000,
            'currency' => 'USD',
        ];
    }

    public function test_create_and_get_by_guid(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(PreventionAlertRepo::class);

        $alert = $repo->create($this->basePayload($tenant->id));
        $fetched = $repo->getByGuid('pa-1');

        $this->assertSame($alert['id'], $fetched['id']);
        $this->assertSame('ethoca', $fetched['prevention_type']);
        $this->assertSame('new', $fetched['stage']);
    }

    public function test_record_resolution_sets_type_and_timestamp(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(PreventionAlertRepo::class);
        $alert = $repo->create($this->basePayload($tenant->id));

        $updated = $repo->recordResolution($alert['id'], 'refund');
        $this->assertSame('refund', $updated['resolution_type']);
        $this->assertNotNull($updated['resolution_submitted_at']);
    }

    public function test_get_for_tenant_filtering(): void
    {
        $t1 = $this->makeTenant();
        $t2 = $this->makeTenant();
        $repo = app(PreventionAlertRepo::class);

        $repo->create($this->basePayload($t1->id, 'a'));
        $repo->create($this->basePayload($t1->id, 'b'));
        $repo->create($this->basePayload($t2->id, 'c'));

        $this->assertCount(2, $repo->getForTenant($t1->id)['items']);
        $this->assertCount(1, $repo->getForTenant($t2->id)['items']);
    }
}
