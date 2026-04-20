<?php

namespace Tests\Feature\Repositories\Activity;

use App\Models\Chargeback;
use App\Repositories\Activity\ActivityLogRepo;
use Tests\Feature\Repositories\RepoTestCase;

class ActivityLogRepoTest extends RepoTestCase
{
    public function test_log_with_loggable_and_metadata(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $cb = Chargeback::create([
            'tenant_id' => $tenant->id,
            'chargeback_guid' => 'cb-a-1',
            'event_guid' => 'evt-a-1',
            'mid' => 'M',
            'amount' => 1,
        ]);
        $repo = app(ActivityLogRepo::class);

        $entry = $repo->log($tenant->id, $user->id, 'viewed', $cb, ['source' => 'spa']);

        $this->assertSame('viewed', $entry['action']);
        $this->assertSame('spa', $entry['metadata']['source']);

        $forModel = $repo->getForModel($cb);
        $this->assertCount(1, $forModel['items']);
    }

    public function test_log_without_loggable(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(ActivityLogRepo::class);

        $entry = $repo->log($tenant->id, $user->id, 'logged_in');
        $this->assertSame('', $entry['loggable_type']);
        $this->assertNull($entry['loggable_id']);
    }

    public function test_get_for_tenant_orders_desc(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(ActivityLogRepo::class);

        $repo->log($tenant->id, $user->id, 'a');
        $repo->log($tenant->id, $user->id, 'b');

        $list = $repo->getForTenant($tenant->id);
        $this->assertCount(2, $list['items']);
    }
}
