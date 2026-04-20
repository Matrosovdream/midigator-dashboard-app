<?php

namespace Tests\Feature\Repositories\Email;

use App\Repositories\Email\EmailTemplateRepo;
use Tests\Feature\Repositories\RepoTestCase;

class EmailTemplateRepoTest extends RepoTestCase
{
    public function test_create_and_fetch_active(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(EmailTemplateRepo::class);

        $repo->create([
            'tenant_id' => $tenant->id,
            'name' => 'Welcome',
            'subject' => 'Hi {{name}}',
            'body' => 'Hello {{name}}',
            'variables' => ['name'],
            'is_active' => true,
        ]);
        $repo->create([
            'tenant_id' => $tenant->id,
            'name' => 'Off',
            'subject' => 's',
            'body' => 'b',
            'is_active' => false,
        ]);

        $active = $repo->getActiveForTenant($tenant->id);
        $this->assertCount(1, $active['items']);
        $this->assertSame('Welcome', $active['items'][0]['name']);
        $this->assertSame(['name'], $active['items'][0]['variables']);
    }

    public function test_get_by_name_for_tenant(): void
    {
        $t1 = $this->makeTenant();
        $t2 = $this->makeTenant();
        $repo = app(EmailTemplateRepo::class);

        $repo->create(['tenant_id' => $t1->id, 'name' => 'X', 'subject' => 's', 'body' => 'b']);
        $repo->create(['tenant_id' => $t2->id, 'name' => 'X', 'subject' => 's2', 'body' => 'b2']);

        $this->assertSame('s', $repo->getByNameForTenant($t1->id, 'X')['subject']);
        $this->assertSame('s2', $repo->getByNameForTenant($t2->id, 'X')['subject']);
    }
}
