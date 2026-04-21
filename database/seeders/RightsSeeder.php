<?php

namespace Database\Seeders;

use App\Models\Right;
use Illuminate\Database\Seeder;

class RightsSeeder extends Seeder
{
    public function run(): void
    {
        $rights = [
            // chargebacks
            ['chargebacks.view', 'View chargebacks', 'View chargeback list and details'],
            ['chargebacks.edit', 'Edit chargebacks', 'Edit chargeback custom fields'],
            ['chargebacks.assign', 'Assign chargebacks', 'Assign chargebacks to managers'],
            ['chargebacks.hide', 'Hide chargebacks', 'Hide/unhide chargebacks from managers'],
            ['chargebacks.stage_change', 'Change chargeback stage', 'Change chargeback workflow stage'],

            // preventions
            ['preventions.view', 'View preventions', 'View prevention alerts'],
            ['preventions.edit', 'Edit preventions', 'Edit prevention custom fields'],
            ['preventions.assign', 'Assign preventions', 'Assign prevention alerts to managers'],
            ['preventions.hide', 'Hide preventions', 'Hide/unhide prevention alerts'],
            ['preventions.resolve', 'Resolve preventions', 'Submit resolution to Midigator API'],
            ['preventions.stage_change', 'Change prevention stage', 'Change prevention workflow stage'],

            // orders
            ['orders.view', 'View orders', 'View orders'],
            ['orders.create', 'Create orders', 'Create new orders and submit to Midigator'],
            ['orders.edit', 'Edit orders', 'Edit order custom fields'],
            ['orders.hide', 'Hide orders', 'Hide/unhide orders'],
            ['orders.submit', 'Submit orders', 'Submit order data to Midigator API'],

            // order_validations
            ['order_validations.view', 'View order validations', 'View order validations'],
            ['order_validations.edit', 'Edit order validations', 'Edit order validation fields'],
            ['order_validations.hide', 'Hide order validations', 'Hide/unhide order validations'],
            ['order_validations.stage_change', 'Change order validation stage', 'Change order validation stage'],

            // rdr
            ['rdr.view', 'View RDR cases', 'View RDR cases'],
            ['rdr.edit', 'Edit RDR cases', 'Edit RDR case fields'],
            ['rdr.hide', 'Hide RDR cases', 'Hide/unhide RDR cases'],
            ['rdr.stage_change', 'Change RDR case stage', 'Change RDR case stage'],

            // comments
            ['comments.view', 'View comments', 'View comments on cases'],
            ['comments.create', 'Create comments', 'Add comments to cases'],
            ['comments.delete', 'Delete comments', 'Delete any comment (not just own)'],

            // emails
            ['emails.view', 'View emails', 'View email logs'],
            ['emails.send', 'Send emails', 'Send emails to customers'],
            ['emails.templates_manage', 'Manage email templates', 'Create/edit/delete email templates'],

            // users
            ['users.view', 'View users', 'View user list'],
            ['users.manage', 'Manage users', 'Create, edit, deactivate users'],
            ['users.invite', 'Invite users', 'Invite new users to tenant'],

            // roles
            ['roles.view', 'View roles', 'View roles and their rights'],
            ['roles.manage', 'Manage roles', 'Create, edit, delete custom roles and assign rights'],

            // managers
            ['managers.score', 'Score managers', 'Set manager performance scores'],
            ['managers.view_profiles', 'View manager profiles', 'View manager profiles and stats'],

            // dashboard
            ['dashboard.view', 'View dashboard', 'View dashboard stats and charts'],
            ['dashboard.manager_performance', 'View manager performance', 'View manager leaderboard and performance'],

            // settings
            ['settings.view', 'View settings', 'View tenant settings'],
            ['settings.manage', 'Manage settings', 'Edit tenant settings, API keys'],

            // activity
            ['activity_log.view', 'View own activity log', 'View own activity log'],
            ['activity_log.view_all', 'View all activity logs', "View all users' activity logs"],

            // notifications
            ['notifications.manage', 'Manage notifications', 'Manage own notification preferences'],

            // evidence
            ['evidence.view', 'View evidence', 'View evidence files attached to cases'],
            ['evidence.upload', 'Upload evidence', 'Upload evidence files to cases'],
            ['evidence.delete', 'Delete evidence', 'Delete evidence files from cases'],

            // export
            ['export.run', 'Run exports', 'Export CSV extracts for tenant data'],
        ];

        foreach ($rights as [$slug, $name, $description]) {
            $group = explode('.', $slug)[0];
            Right::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'group' => $group, 'description' => $description],
            );
        }
    }
}
