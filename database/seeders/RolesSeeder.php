<?php

namespace Database\Seeders;

use App\Models\Right;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $managerRights = [
            'chargebacks.view', 'chargebacks.edit', 'chargebacks.stage_change',
            'preventions.view', 'preventions.edit', 'preventions.resolve', 'preventions.stage_change',
            'orders.view', 'orders.edit',
            'order_validations.view', 'order_validations.edit', 'order_validations.stage_change',
            'rdr.view', 'rdr.edit', 'rdr.stage_change',
            'comments.view', 'comments.create',
            'emails.view', 'emails.send',
            'dashboard.view',
            'activity_log.view',
            'notifications.manage',
        ];

        $allRightIds = Right::pluck('id')->all();
        $managerRightIds = Right::whereIn('slug', $managerRights)->pluck('id')->all();

        foreach ($tenants as $tenant) {
            $admin = Role::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'tenant-admin'],
                [
                    'name' => 'Tenant Admin',
                    'description' => 'Built-in role with all rights. Cannot be deleted.',
                    'is_system' => true,
                ],
            );
            $admin->rights()->sync($allRightIds);

            $manager = Role::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'manager'],
                [
                    'name' => 'Manager',
                    'description' => 'Built-in role with limited rights. Cannot be deleted.',
                    'is_system' => true,
                ],
            );
            $manager->rights()->sync($managerRightIds);
        }
    }
}
