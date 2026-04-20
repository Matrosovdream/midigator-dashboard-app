<?php

namespace Tests\Feature\Repositories\Rdr;

use App\Repositories\Rdr\RdrCaseRepo;
use Tests\Feature\Repositories\RepoTestCase;

class RdrCaseRepoTest extends RepoTestCase
{
    public function test_create_get_by_guid_and_record_resolution(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(RdrCaseRepo::class);

        $rdr = $repo->create([
            'tenant_id' => $tenant->id,
            'rdr_guid' => 'rdr-1',
            'event_guid' => 'evt-rdr-1',
            'amount' => 7777,
        ]);

        $this->assertSame($rdr['id'], $repo->getByGuid('rdr-1')['id']);

        $resolved = $repo->recordResolution($rdr['id'], 'accepted');
        $this->assertSame('accepted', $resolved['rdr_resolution']);
    }

    public function test_get_for_tenant_filters(): void
    {
        $t = $this->makeTenant();
        $other = $this->makeTenant();
        $repo = app(RdrCaseRepo::class);

        $repo->create(['tenant_id' => $t->id, 'rdr_guid' => 'a', 'event_guid' => 'ea', 'amount' => 1]);
        $repo->create(['tenant_id' => $other->id, 'rdr_guid' => 'b', 'event_guid' => 'eb', 'amount' => 2]);

        $this->assertCount(1, $repo->getForTenant($t->id)['items']);
    }
}
