<?php

namespace Tests\Feature\Repositories\Tenant;

use App\Repositories\Tenant\TenantRepo;
use Tests\Feature\Repositories\RepoTestCase;

class TenantRepoTest extends RepoTestCase
{
    public function test_creates_and_fetches_tenant(): void
    {
        $repo = app(TenantRepo::class);

        $created = $repo->create([
            'name' => 'Acme',
            'slug' => 'acme-co',
            'is_active' => true,
            'midigator_sandbox_mode' => true,
        ]);

        $this->assertNotNull($created);
        $this->assertSame('acme-co', $created['slug']);
        $this->assertTrue($created['midigator_sandbox_mode']);

        $fetched = $repo->getByID($created['id']);
        $this->assertSame($created['id'], $fetched['id']);
        $this->assertSame('Acme', $fetched['name']);
    }

    public function test_get_by_slug_and_domain(): void
    {
        $repo = app(TenantRepo::class);
        $repo->create(['name' => 'X', 'slug' => 'x-co', 'domain' => 'x.test', 'is_active' => true]);

        $this->assertSame('x-co', $repo->getBySlug('x-co')['slug']);
        $this->assertSame('x.test', $repo->getByDomain('x.test')['domain']);
        $this->assertNull($repo->getByDomain('missing.test'));
    }

    public function test_get_active_filters_inactive(): void
    {
        $repo = app(TenantRepo::class);
        $repo->create(['name' => 'On', 'slug' => 'on-co', 'is_active' => true]);
        $repo->create(['name' => 'Off', 'slug' => 'off-co', 'is_active' => false]);

        $result = $repo->getActive();
        $this->assertCount(1, $result['items']);
        $this->assertSame('on-co', $result['items'][0]['slug']);
    }

    public function test_update_and_delete(): void
    {
        $repo = app(TenantRepo::class);
        $tenant = $repo->create(['name' => 'A', 'slug' => 'a-co', 'is_active' => true]);

        $updated = $repo->update($tenant['id'], ['name' => 'A2']);
        $this->assertSame('A2', $updated['name']);

        $this->assertTrue($repo->delete($tenant['id']));
        $this->assertNull($repo->getByID($tenant['id']));
    }

    public function test_encrypted_secret_roundtrip(): void
    {
        $repo = app(TenantRepo::class);
        $tenant = $repo->create([
            'name' => 'Sec',
            'slug' => 'sec-co',
            'is_active' => true,
            'midigator_api_secret' => 'super-secret-token',
        ]);

        $fetched = $repo->getByID($tenant['id']);
        $this->assertSame('super-secret-token', $fetched['Model']->midigator_api_secret);
    }
}
