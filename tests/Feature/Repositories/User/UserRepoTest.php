<?php

namespace Tests\Feature\Repositories\User;

use App\Repositories\User\UserRepo;
use Tests\Feature\Repositories\RepoTestCase;

class UserRepoTest extends RepoTestCase
{
    public function test_create_and_get_by_email(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(UserRepo::class);

        $created = $repo->create([
            'tenant_id' => $tenant->id,
            'email' => 'a@test.local',
            'password' => 'secret',
            'name' => 'Alice',
        ]);

        $found = $repo->getByEmail('a@test.local');
        $this->assertSame($created['id'], $found['id']);
        $this->assertSame($tenant->id, $found['tenant_id']);
        $this->assertSame('Alice', $found['name']);
        $this->assertIsArray($found['roles']);
    }

    public function test_password_is_hashed_and_hidden(): void
    {
        $repo = app(UserRepo::class);
        $user = $repo->create(['email' => 'h@test.local', 'password' => 'plain', 'name' => 'H']);

        $this->assertArrayNotHasKey('password', $user);
        $this->assertNotSame('plain', $user['Model']->getAttributes()['password']);
        $this->assertTrue(password_verify('plain', $user['Model']->getAttributes()['password']));
    }

    public function test_touch_last_login(): void
    {
        $repo = app(UserRepo::class);
        $user = $repo->create(['email' => 't@test.local', 'password' => 's', 'name' => 'T']);
        $this->assertNull($user['last_login_at']);

        $repo->touchLastLogin($user['id']);
        $this->assertNotNull($repo->getByID($user['id'])['last_login_at']);
    }

    public function test_get_by_tenant_filters_correctly(): void
    {
        $t1 = $this->makeTenant();
        $t2 = $this->makeTenant();
        $repo = app(UserRepo::class);

        $repo->create(['tenant_id' => $t1->id, 'email' => '1@test.local', 'password' => 'x', 'name' => 'A']);
        $repo->create(['tenant_id' => $t1->id, 'email' => '2@test.local', 'password' => 'x', 'name' => 'B']);
        $repo->create(['tenant_id' => $t2->id, 'email' => '3@test.local', 'password' => 'x', 'name' => 'C']);

        $this->assertCount(2, $repo->getByTenant($t1->id)['items']);
        $this->assertCount(1, $repo->getByTenant($t2->id)['items']);
    }
}
