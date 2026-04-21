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
}

const auth = useAuthStore();

const tenantMenu: MenuItem[] = [
    {
        label: 'Home',
        items: [{ label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }],
    },
    {
        label: 'Administration',
        items: [{ label: 'Users', icon: 'pi pi-fw pi-users', to: '/users' }],
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

const model = computed<MenuItem[]>(() => (auth.user?.is_platform_admin ? platformMenu : tenantMenu));
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item.label ?? i">
            <app-menu-item v-if="!item.separator" :item="item" :index="i" />
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>
