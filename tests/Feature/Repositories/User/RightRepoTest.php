<?php

namespace Tests\Feature\Repositories\User;

use App\Repositories\User\RightRepo;
use Tests\Feature\Repositories\RepoTestCase;

class RightRepoTest extends RepoTestCase
{
    public function test_create_and_get_by_group(): void
    {
        $repo = app(RightRepo::class);
        $repo->create(['name' => 'View', 'slug' => 'cb.view', 'group' => 'cb']);
        $repo->create(['name' => 'Edit', 'slug' => 'cb.edit', 'group' => 'cb']);
        $repo->create(['name' => 'Send', 'slug' => 'mail.send', 'group' => 'mail']);

        $this->assertCount(2, $repo->getByGroup('cb')['items']);
        $this->assertCount(1, $repo->getByGroup('mail')['items']);
    }

    public function test_get_by_slugs(): void
    {
        $repo = app(RightRepo::class);
        $repo->create(['name' => 'A', 'slug' => 'a.x', 'group' => 'a']);
        $repo->create(['name' => 'B', 'slug' => 'b.x', 'group' => 'b']);
        $repo->create(['name' => 'C', 'slug' => 'c.x', 'group' => 'c']);

        $hits = $repo->getBySlugs(['a.x', 'c.x', 'missing']);
        $this->assertCount(2, $hits);
        $this->assertEqualsCanonicalizing(['a.x', 'c.x'], array_column($hits, 'slug'));
    }

    public function test_unique_slug_constraint(): void
    {
        $repo = app(RightRepo::class);
        $repo->create(['name' => 'A', 'slug' => 'unique.slug', 'group' => 'g']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $repo->create(['name' => 'B', 'slug' => 'unique.slug', 'group' => 'g']);
    }
}
