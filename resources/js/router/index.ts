import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/',
        component: () => import('@/layout/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: { name: 'dashboard' } },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('@/views/dashboard/DashboardHomeView.vue'),
            },
            {
                path: 'users',
                name: 'users.index',
                component: () => import('@/views/user/UserListView.vue'),
            },
            {
                path: 'platform-users',
                name: 'platform.users.index',
                component: () => import('@/views/platform/PlatformUserListView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'integrations',
                name: 'platform.integrations.index',
                component: () => import('@/views/platform/IntegrationHealthView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'webhook-logs',
                name: 'platform.webhooks.index',
                component: () => import('@/views/platform/WebhookLogListView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'rights',
                name: 'platform.rights.index',
                component: () => import('@/views/platform/RightsCatalogView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'activity',
                name: 'platform.activity.index',
                component: () => import('@/views/platform/GlobalActivityLogView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'emails/logs',
                name: 'platform.emails.logs.index',
                component: () => import('@/views/platform/EmailLogListView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'emails/templates',
                name: 'platform.emails.templates.index',
                component: () => import('@/views/platform/EmailTemplateListView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'platform-settings',
                name: 'platform.settings.index',
                component: () => import('@/views/platform/PlatformSettingsView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'tenants',
                name: 'platform.tenants.index',
                component: () => import('@/views/platform/TenantListView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'tenants/new',
                name: 'platform.tenants.create',
                component: () => import('@/views/platform/TenantFormView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'tenants/:id',
                name: 'platform.tenants.show',
                component: () => import('@/views/platform/TenantDetailView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
            {
                path: 'tenants/:id/edit',
                name: 'platform.tenants.edit',
                component: () => import('@/views/platform/TenantFormView.vue'),
                meta: { requiresPlatformAdmin: true },
            },
        ],
    },
    { path: '/:pathMatch(.*)*', redirect: { name: 'login' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

let bootstrapped = false;

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!bootstrapped) {
        await auth.fetchMe();
        bootstrapped = true;
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.requiresPlatformAdmin && !auth.user?.is_platform_admin) {
        return { name: 'dashboard' };
    }
});

export default router;
