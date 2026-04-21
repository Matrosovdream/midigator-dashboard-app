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
            'chargebacks.view', 'chargebacks.edit', 'chargebacks.stage_change', 'chargebacks.assign',
            'preventions.view', 'preventions.edit', 'preventions.resolve', 'preventions.stage_change', 'preventions.assign',
            'orders.view', 'orders.create', 'orders.edit', 'orders.submit',
            'order_validations.view', 'order_validations.edit', 'order_validations.stage_change',
            'rdr.view', 'rdr.edit', 'rdr.stage_change', 'rdr.assign',
            'comments.view', 'comments.create',
            'emails.view', 'emails.send',
            'dashboard.view',
            'activity_log.view',
            'notifications.manage',
            'export.run',
            'users.view',
            'evidence.view', 'evidence.upload', 'evidence.delete',
        ];

        $analystRights = [
            'chargebacks.view', 'chargebacks.edit',
            'preventions.view', 'preventions.edit',
            'orders.view',
            'order_validations.view', 'order_validations.edit',
            'rdr.view', 'rdr.edit',
            'comments.view', 'comments.create',
            'dashboard.view',
            'export.run',
            'evidence.view', 'evidence.upload',
        ];

        $viewerRights = [
            'chargebacks.view',
            'preventions.view',
            'orders.view',
            'order_validations.view',
            'rdr.view',
            'comments.view',
            'dashboard.view',
            'evidence.view',
        ];

        $allRightIds = Right::pluck('id')->all();
        $managerRightIds = Right::whereIn('slug', $managerRights)->pluck('id')->all();
        $analystRightIds = Right::whereIn('slug', $analystRights)->pluck('id')->all();
        $viewerRightIds = Right::whereIn('slug', $viewerRights)->pluck('id')->all();

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

            $analyst = Role::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'analyst'],
                [
                    'name' => 'Analyst',
                    'description' => 'Works cases but cannot assign or change stages owned by managers.',
                    'is_system' => true,
                ],
            );
            $analyst->rights()->sync($analystRightIds);

            $viewer = Role::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => 'viewer'],
                [
                    'name' => 'Viewer',
                    'description' => 'Read-only access to cases and dashboard.',
                    'is_system' => true,
                ],
            );
            $viewer->rights()->sync($viewerRightIds);
        }
    }
}
