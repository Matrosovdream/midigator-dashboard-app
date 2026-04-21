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

        $users = [
            ['email' => 'admin@midigator.test',   'name' => 'Demo Admin',   'pin' => '1234', 'role' => 'tenant-admin'],
            ['email' => 'manager@midigator.test', 'name' => 'Demo Manager', 'pin' => '5678', 'role' => 'manager'],
            ['email' => 'analyst@midigator.test', 'name' => 'Demo Analyst', 'pin' => '4321', 'role' => 'analyst'],
            ['email' => 'viewer@midigator.test',  'name' => 'Demo Viewer',  'pin' => '8765', 'role' => 'viewer'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'pin' => Hash::make($u['pin']),
                    'is_active' => true,
                    'is_platform_admin' => false,
                ],
            );

            $role = Role::where('tenant_id', $tenant->id)->where('slug', $u['role'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }

        User::updateOrCreate(
            ['email' => 'platform@midigator.test'],
            [
                'tenant_id' => null,
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'pin' => Hash::make('9999'),
                'is_active' => true,
                'is_platform_admin' => true,
            ],
        );
    }
}
