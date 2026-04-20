<?php

namespace Tests\Feature\Repositories\User;

use App\Repositories\User\ManagerProfileRepo;
use Tests\Feature\Repositories\RepoTestCase;

class ManagerProfileRepoTest extends RepoTestCase
{
    public function test_upsert_creates_then_updates(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser($tenant->id);
        $repo = app(ManagerProfileRepo::class);

        $created = $repo->upsertForUser($user->id, ['score' => 3.50, 'notes' => 'okay']);
        $this->assertSame(3.5, $created['score']);

        $updated = $repo->upsertForUser($user->id, ['score' => 4.75, 'notes' => 'better']);
        $this->assertSame(4.75, $updated['score']);
        $this->assertSame(1, $repo->count(['user_id' => $user->id]));
    }

    public function test_assigned_mids_roundtrip_as_array(): void
    {
        $user = $this->makeUser($this->makeTenant()->id);
        $repo = app(ManagerProfileRepo::class);

        $repo->upsertForUser($user->id, ['assigned_mids' => ['MID-1', 'MID-2']]);

        $fetched = $repo->getByUserID($user->id);
        $this->assertSame(['MID-1', 'MID-2'], $fetched['assigned_mids']);
    }

    public function test_eager_loaded_user_in_map_item(): void
    {
        $user = $this->makeUser($this->makeTenant()->id, ['name' => 'Carol']);
        $repo = app(ManagerProfileRepo::class);

        $created = $repo->upsertForUser($user->id, ['score' => 5.0]);
        $this->assertSame('Carol', $created['user']['name']);
    }
}
