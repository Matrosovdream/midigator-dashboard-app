<?php

namespace Tests\Feature\Repositories\User;

use App\Repositories\User\RightRepo;
use App\Repositories\User\RoleRepo;
use Tests\Feature\Repositories\RepoTestCase;

class RoleRepoTest extends RepoTestCase
{
    public function test_create_and_fetch_role_with_rights(): void
    {
        $tenant = $this->makeTenant();
        $roles = app(RoleRepo::class);
        $rights = app(RightRepo::class);

        $role = $roles->create([
            'tenant_id' => $tenant->id,
            'name' => 'Manager',
            'slug' => 'manager',
        ]);
        $r1 = $rights->create(['name' => 'View CB', 'slug' => 'cb.view', 'group' => 'cb']);
        $r2 = $rights->create(['name' => 'Edit CB', 'slug' => 'cb.edit', 'group' => 'cb']);

        $roles->syncRights($role['id'], [$r1['id'], $r2['id']]);

        $fetched = $roles->getByID($role['id']);
        $this->assertCount(2, $fetched['rights']);
        $this->assertEqualsCanonicalizing(['cb.view', 'cb.edit'], array_column($fetched['rights'], 'slug'));
    }

    public function test_get_by_slug_for_tenant(): void
    {
        $t1 = $this->makeTenant();
        $t2 = $this->makeTenant();
        $roles = app(RoleRepo::class);

        $roles->create(['tenant_id' => $t1->id, 'name' => 'A', 'slug' => 'admin']);
        $roles->create(['tenant_id' => $t2->id, 'name' => 'B', 'slug' => 'admin']);

        $this->assertSame('A', $roles->getBySlugForTenant('admin', $t1->id)['name']);
        $this->assertSame('B', $roles->getBySlugForTenant('admin', $t2->id)['name']);
    }

    public function test_get_by_tenant(): void
    {
        $tenant = $this->makeTenant();
        $roles = app(RoleRepo::class);

        $roles->create(['tenant_id' => $tenant->id, 'name' => 'A', 'slug' => 'a']);
        $roles->create(['tenant_id' => $tenant->id, 'name' => 'B', 'slug' => 'b']);
        $roles->create(['tenant_id' => null, 'name' => 'Sys', 'slug' => 'sys']);

        $this->assertCount(2, $roles->getByTenant($tenant->id)['items']);
        $this->assertCount(1, $roles->getByTenant(null)['items']);
    }

    public function test_sync_rights_replaces_existing(): void
    {
        $roles = app(RoleRepo::class);
        $rights = app(RightRepo::class);
        $role = $roles->create(['name' => 'R', 'slug' => 'r']);
        $r1 = $rights->create(['name' => 'X', 'slug' => 'x', 'group' => 'g']);
        $r2 = $rights->create(['name' => 'Y', 'slug' => 'y', 'group' => 'g']);

        $roles->syncRights($role['id'], [$r1['id']]);
        $this->assertCount(1, $roles->getByID($role['id'])['rights']);

        $roles->syncRights($role['id'], [$r2['id']]);
        $rights2 = $roles->getByID($role['id'])['rights'];
        $this->assertCount(1, $rights2);
        $this->assertSame('y', $rights2[0]['slug']);
    }
}
