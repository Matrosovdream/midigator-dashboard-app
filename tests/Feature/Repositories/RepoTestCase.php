<?php

namespace Tests\Feature\Repositories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class RepoTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Acme Corp',
            'slug' => 'acme-'.uniqid(),
            'is_active' => true,
            'midigator_sandbox_mode' => false,
        ], $overrides));
    }

    protected function makeUser(?int $tenantId = null, array $overrides = []): User
    {
        return User::create(array_merge([
            'tenant_id' => $tenantId,
            'email' => 'user-'.uniqid().'@test.local',
            'password' => 'secret',
            'name' => 'Test User',
            'is_active' => true,
            'is_platform_admin' => false,
        ], $overrides));
    }
}
