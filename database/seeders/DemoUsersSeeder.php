<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->first();
        if (!$tenant) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@midigator.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_platform_admin' => false,
            ],
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@midigator.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Manager',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_platform_admin' => false,
            ],
        );

        User::firstOrCreate(
            ['email' => 'platform@midigator.test'],
            [
                'tenant_id' => null,
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_platform_admin' => true,
            ],
        );

        $adminRole = Role::where('tenant_id', $tenant->id)->where('slug', 'tenant-admin')->first();
        $managerRole = Role::where('tenant_id', $tenant->id)->where('slug', 'manager')->first();

        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
        if ($managerRole) {
            $manager->roles()->syncWithoutDetaching([$managerRole->id]);
        }
    }
}
