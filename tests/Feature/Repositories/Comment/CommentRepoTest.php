<?php

namespace Tests\Feature\Repositories\Comment;

use App\Models\Chargeback;
use App\Repositories\Comment\CommentRepo;
use Tests\Feature\Repositories\RepoTestCase;

class CommentRepoTest extends RepoTestCase
{
    private function makeChargeback(int $tenantId): Chargeback
    {
        return Chargeback::create([
            'tenant_id' => $tenantId,
            'chargeback_guid' => 'cb-c-'.uniqid(),
            'event_guid' => 'evt-c',
            'mid' => 'M',
            'amount' => 100,
        ]);
    }

    public function test_add_to_model_and_fetch_for_model(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id, ['name' => 'Author']);
        $cb = $this->makeChargeback($tenant->id);
        $repo = app(CommentRepo::class);

        $repo->addToModel($cb, $user->id, 'first comment');
        $repo->addToModel($cb, $user->id, 'internal note', isInternal: true);

        $all = $repo->getForModel($cb);
        $this->assertCount(2, $all['items']);
        $this->assertSame('Author', $all['items'][0]['user']['name']);

        $publicOnly = $repo->getForModel($cb, includeInternal: false);
        $this->assertCount(1, $publicOnly['items']);
        $this->assertSame('first comment', $publicOnly['items'][0]['body']);
    }

    public function test_polymorphic_isolation(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $cbA = $this->makeChargeback($tenant->id);
        $cbB = $this->makeChargeback($tenant->id);
        $repo = app(CommentRepo::class);

        $repo->addToModel($cbA, $user->id, 'a');
        $repo->addToModel($cbB, $user->id, 'b');

        $this->assertCount(1, $repo->getForModel($cbA)['items']);
        $this->assertCount(1, $repo->getForModel($cbB)['items']);
    }
}
