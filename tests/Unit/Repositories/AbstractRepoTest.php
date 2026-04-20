<?php

namespace Tests\Unit\Repositories;

use App\Repositories\Tenant\TenantRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbstractRepoTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_operators(): void
    {
        $repo = app(TenantRepo::class);

        $repo->create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);
        $repo->create(['name' => 'B', 'slug' => 'b', 'is_active' => false]);
        $repo->create(['name' => 'C', 'slug' => 'c', 'is_active' => true]);

        $this->assertSame(2, $repo->count(['is_active' => true]));
        $this->assertSame(2, $repo->count(['id' => ['CONDITION' => 'IN', 1, 2]]));
        $this->assertSame(1, $repo->count(['id' => ['CONDITION' => 'BETWEEN', 1, 1]]));
        $this->assertSame(1, $repo->count(['name' => 'A%']));
        $this->assertTrue($repo->exists(['slug' => 'a']));
        $this->assertFalse($repo->exists(['slug' => 'missing']));
    }

    public function test_set_relations_alters_eager_loading(): void
    {
        $repo = app(UserRepo::class);
        $base = $repo->getModel();

        $repo->setRelations(['roles']);
        $reflection = new \ReflectionProperty($repo, 'withRelations');
        $reflection->setAccessible(true);
        $this->assertSame(['roles'], $reflection->getValue($repo));
        $this->assertSame($base, $repo->getModel());
    }

    public function test_scoped_binding_returns_same_instance_within_request(): void
    {
        $a = app(UserRepo::class);
        $b = app(UserRepo::class);
        $this->assertSame($a, $b);
    }

    public function test_get_by_id_returns_null_for_missing(): void
    {
        $this->assertNull(app(TenantRepo::class)->getByID(999_999));
    }

    public function test_delete_returns_false_for_missing(): void
    {
        $this->assertFalse(app(TenantRepo::class)->delete(999_999));
    }

    public function test_update_returns_null_for_missing(): void
    {
        $this->assertNull(app(TenantRepo::class)->update(999_999, ['name' => 'x']));
    }
}
