<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppMenuItem from './AppMenuItem.vue';

interface MenuItem {
    label?: string;
    icon?: string;
    to?: string;
    separator?: boolean;
    items?: MenuItem[];
    right?: string;
}

const auth = useAuthStore();

function canSee(item: MenuItem): boolean {
    if (!item.right) return true;
    return auth.can(item.right);
}

function filterMenu(menu: MenuItem[]): MenuItem[] {
    return menu
        .map((group) => ({
            ...group,
            items: group.items?.filter(canSee),
        }))
        .filter((group) => !group.items || group.items.length > 0);
}

const tenantMenu: MenuItem[] = [
    {
        label: 'Home',
        items: [{ label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }],
    },
    {
        label: 'Cases',
        items: [
            { label: 'Orders', icon: 'pi pi-fw pi-shopping-cart', to: '/orders', right: 'orders.view' },
            { label: 'Chargebacks', icon: 'pi pi-fw pi-credit-card', to: '/chargebacks', right: 'chargebacks.view' },
            { label: 'Preventions', icon: 'pi pi-fw pi-shield', to: '/preventions', right: 'preventions.view' },
            { label: 'RDR Cases', icon: 'pi pi-fw pi-flag', to: '/rdr', right: 'rdr.view' },
        ],
    },
    {
        label: 'Operations',
        items: [
            { label: 'Validations', icon: 'pi pi-fw pi-check-square', to: '/validations', right: 'order_validations.view' },
            { label: 'Exports', icon: 'pi pi-fw pi-download', to: '/exports', right: 'export.run' },
            { label: 'Search', icon: 'pi pi-fw pi-search', to: '/search' },
        ],
    },
    {
        label: 'Team',
        items: [
            { label: 'Users', icon: 'pi pi-fw pi-users', to: '/team/users', right: 'users.view' },
            { label: 'Managers', icon: 'pi pi-fw pi-user', to: '/team/managers', right: 'dashboard.manager_performance' },
            { label: 'Roles & Rights', icon: 'pi pi-fw pi-key', to: '/team/roles' },
        ],
    },
    {
        label: 'Communications',
        items: [
            { label: 'Email Templates', icon: 'pi pi-fw pi-envelope', to: '/comms/templates', right: 'emails.view' },
            { label: 'Notifications', icon: 'pi pi-fw pi-bell', to: '/notifications' },
            { label: 'Notification Settings', icon: 'pi pi-fw pi-sliders-h', to: '/notifications/settings' },
        ],
    },
    {
        label: 'Settings',
        items: [
            { label: 'Tenant Profile', icon: 'pi pi-fw pi-building', to: '/settings/profile' },
            { label: 'Integrations', icon: 'pi pi-fw pi-link', to: '/settings/integrations' },
            { label: 'Activity Log', icon: 'pi pi-fw pi-list', to: '/settings/activity', right: 'activity_log.view' },
            { label: 'Preferences', icon: 'pi pi-fw pi-cog', to: '/settings/preferences' },
        ],
    },
];

const managerMenu: MenuItem[] = [
    {
        label: 'Home',
        items: [{ label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/manager' }],
    },
    {
        label: 'Team',
        items: [
            { label: 'My team', icon: 'pi pi-fw pi-users', to: '/manager/team', right: 'managers.view_profiles' },
            { label: 'Performance', icon: 'pi pi-fw pi-chart-bar', to: '/manager/performance', right: 'dashboard.manager_performance' },
        ],
    },
    {
        label: 'Workflows',
        items: [
            { label: 'Assignments', icon: 'pi pi-fw pi-inbox', to: '/manager/assignments', right: 'chargebacks.assign' },
            { label: 'Approvals', icon: 'pi pi-fw pi-check-circle', to: '/manager/approvals', right: 'chargebacks.stage_change' },
        ],
    },
    {
        label: 'Cases',
        items: [
            { label: 'Chargebacks', icon: 'pi pi-fw pi-credit-card', to: '/chargebacks', right: 'chargebacks.view' },
            { label: 'Preventions', icon: 'pi pi-fw pi-shield', to: '/preventions', right: 'preventions.view' },
            { label: 'RDR Cases', icon: 'pi pi-fw pi-flag', to: '/rdr', right: 'rdr.view' },
            { label: 'Orders', icon: 'pi pi-fw pi-shopping-cart', to: '/orders', right: 'orders.view' },
        ],
    },
    {
        label: 'Operations',
        items: [
            { label: 'Exports', icon: 'pi pi-fw pi-download', to: '/exports', right: 'export.run' },
            { label: 'Search', icon: 'pi pi-fw pi-search', to: '/search' },
            { label: 'Team activity', icon: 'pi pi-fw pi-list', to: '/manager/activity', right: 'activity_log.view_all' },
        ],
    },
    {
        label: 'Settings',
        items: [
            { label: 'Notifications', icon: 'pi pi-fw pi-bell', to: '/notifications' },
            { label: 'Preferences', icon: 'pi pi-fw pi-cog', to: '/settings/preferences' },
        ],
    },
];

const platformMenu: MenuItem[] = [
    {
        label: 'Home',
        items: [{ label: 'Overview', icon: 'pi pi-fw pi-home', to: '/dashboard' }],
    },
    {
        label: 'Platform',
        items: [
            { label: 'Tenants', icon: 'pi pi-fw pi-building', to: '/tenants' },
            { label: 'Platform Users', icon: 'pi pi-fw pi-users', to: '/platform-users' },
            { label: 'Integrations', icon: 'pi pi-fw pi-link', to: '/integrations' },
            { label: 'Webhook Logs', icon: 'pi pi-fw pi-bolt', to: '/webhook-logs' },
        ],
    },
    {
        label: 'Comms',
        items: [
            { label: 'Email Logs', icon: 'pi pi-fw pi-envelope', to: '/emails/logs' },
            { label: 'Email Templates', icon: 'pi pi-fw pi-file-edit', to: '/emails/templates' },
        ],
    },
    {
        label: 'System',
        items: [
            { label: 'Rights Catalog', icon: 'pi pi-fw pi-shield', to: '/rights' },
            { label: 'Activity Log', icon: 'pi pi-fw pi-list', to: '/activity' },
            { label: 'Settings', icon: 'pi pi-fw pi-cog', to: '/platform-settings' },
        ],
    },
];

const model = computed<MenuItem[]>(() => {
    if (auth.persona === 'platform') return platformMenu;
    if (auth.persona === 'manager') return filterMenu(managerMenu);
    return filterMenu(tenantMenu);
});
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item.label ?? i">
            <app-menu-item v-if="!item.separator" :item="item" :index="i" />
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>
