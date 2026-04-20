<?php

namespace Tests\Feature\Repositories\Workflow;

use App\Models\Chargeback;
use App\Repositories\Workflow\StageTransitionRepo;
use Tests\Feature\Repositories\RepoTestCase;

class StageTransitionRepoTest extends RepoTestCase
{
    public function test_record_and_fetch_history(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $cb = Chargeback::create([
            'tenant_id' => $tenant->id,
            'chargeback_guid' => 'cb-st-1',
            'event_guid' => 'evt-st-1',
            'mid' => 'M',
            'amount' => 1,
        ]);
        $repo = app(StageTransitionRepo::class);

        $repo->record($cb, $user->id, 'new', 'in_review', 'starting');
        $repo->record($cb, $user->id, 'in_review', 'resolved', 'done');

        $history = $repo->getForModel($cb);
        $this->assertCount(2, $history['items']);
        $this->assertSame('resolved', $history['items'][0]['to_stage']);
        $this->assertSame('starting', $history['items'][1]['notes']);
    }
}
