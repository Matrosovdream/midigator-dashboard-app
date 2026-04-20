<?php

namespace Tests\Feature\Repositories\Tenant;

use App\Repositories\Tenant\SiteSettingRepo;
use Tests\Feature\Repositories\RepoTestCase;

class SiteSettingRepoTest extends RepoTestCase
{
    public function test_set_and_get_by_key_per_tenant(): void
    {
        $tenant = $this->makeTenant();
        $repo = app(SiteSettingRepo::class);

        $repo->setValue($tenant->id, 'theme', 'dark', 'string', 'appearance');
        $repo->setValue(null, 'theme', 'light', 'string', 'appearance');

        $tenantSetting = $repo->getByKey($tenant->id, 'theme');
        $platformSetting = $repo->getByKey(null, 'theme');

        $this->assertSame('dark', $tenantSetting['value']);
        $this->assertSame('light', $platformSetting['value']);
    }

    public function test_typed_values_cast_correctly(): void
    {
        $repo = app(SiteSettingRepo::class);

        $repo->setValue(null, 'max_per_page', '50', 'integer');
        $repo->setValue(null, 'is_open', '1', 'boolean');
        $repo->setValue(null, 'palette', ['#fff', '#000'], 'json');

        $this->assertSame(50, $repo->getByKey(null, 'max_per_page')['value']);
        $this->assertTrue($repo->getByKey(null, 'is_open')['value']);
        $this->assertSame(['#fff', '#000'], $repo->getByKey(null, 'palette')['value']);
    }

    public function test_set_value_is_idempotent_per_key(): void
    {
        $repo = app(SiteSettingRepo::class);

        $repo->setValue(null, 'mode', 'a');
        $repo->setValue(null, 'mode', 'b');

        $this->assertSame('b', $repo->getByKey(null, 'mode')['value']);
        $this->assertSame(1, $repo->count(['key' => 'mode']));
    }

    public function test_get_by_group(): void
    {
        $repo = app(SiteSettingRepo::class);
        $repo->setValue(null, 'a', '1', 'integer', 'mail');
        $repo->setValue(null, 'b', '2', 'integer', 'mail');
        $repo->setValue(null, 'c', '3', 'integer', 'general');

        $result = $repo->getByGroup(null, 'mail');
        $this->assertCount(2, $result['items']);
    }
}
