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

            // ---------- Tenant dashboard (non-platform-admin users) ----------
            {
                path: 'orders',
                name: 'tenant.orders.index',
                component: () => import('@/views/tenant/OrderListView.vue'),
                meta: { requiresTenantUser: true, right: 'orders.view' },
            },
            {
                path: 'orders/new',
                name: 'tenant.orders.create',
                component: () => import('@/views/tenant/OrderFormView.vue'),
                meta: { requiresTenantUser: true, right: 'orders.create' },
            },
            {
                path: 'orders/:id',
                name: 'tenant.orders.show',
                component: () => import('@/views/tenant/OrderDetailView.vue'),
                meta: { requiresTenantUser: true, right: 'orders.view' },
            },
            {
                path: 'chargebacks',
                name: 'tenant.chargebacks.index',
                component: () => import('@/views/tenant/ChargebackListView.vue'),
                meta: { requiresTenantUser: true, right: 'chargebacks.view' },
            },
            {
                path: 'chargebacks/:id',
                name: 'tenant.chargebacks.show',
                component: () => import('@/views/tenant/ChargebackDetailView.vue'),
                meta: { requiresTenantUser: true, right: 'chargebacks.view' },
            },
            {
                path: 'preventions',
                name: 'tenant.preventions.index',
                component: () => import('@/views/tenant/PreventionListView.vue'),
                meta: { requiresTenantUser: true, right: 'preventions.view' },
            },
            {
                path: 'preventions/:id',
                name: 'tenant.preventions.show',
                component: () => import('@/views/tenant/PreventionDetailView.vue'),
                meta: { requiresTenantUser: true, right: 'preventions.view' },
            },
            {
                path: 'rdr',
                name: 'tenant.rdr.index',
                component: () => import('@/views/tenant/RdrListView.vue'),
                meta: { requiresTenantUser: true, right: 'rdr.view' },
            },
            {
                path: 'rdr/:id',
                name: 'tenant.rdr.show',
                component: () => import('@/views/tenant/RdrDetailView.vue'),
                meta: { requiresTenantUser: true, right: 'rdr.view' },
            },
            {
                path: 'validations',
                name: 'tenant.validations.index',
                component: () => import('@/views/tenant/OrderValidationListView.vue'),
                meta: { requiresTenantUser: true, right: 'order_validations.view' },
            },
            {
                path: 'exports',
                name: 'tenant.exports.index',
                component: () => import('@/views/tenant/ExportsView.vue'),
                meta: { requiresTenantUser: true, right: 'export.run' },
            },
            {
                path: 'search',
                name: 'tenant.search.index',
                component: () => import('@/views/tenant/GlobalSearchView.vue'),
                meta: { requiresTenantUser: true },
            },
            {
                path: 'team/users',
                name: 'tenant.team.users.index',
                component: () => import('@/views/tenant/TeamUserListView.vue'),
                meta: { requiresTenantUser: true, right: 'users.view' },
            },
            {
                path: 'team/managers',
                name: 'tenant.team.managers.index',
                component: () => import('@/views/tenant/ManagerProfilesView.vue'),
                meta: { requiresTenantUser: true, right: 'dashboard.manager_performance' },
            },
            {
                path: 'team/roles',
                name: 'tenant.team.roles.index',
                component: () => import('@/views/tenant/RolesView.vue'),
                meta: { requiresTenantUser: true },
            },
            {
                path: 'comms/templates',
                name: 'tenant.emails.templates.index',
                component: () => import('@/views/tenant/EmailTemplatesView.vue'),
                meta: { requiresTenantUser: true, right: 'emails.view' },
            },
            {
                path: 'notifications',
                name: 'tenant.notifications.index',
                component: () => import('@/views/tenant/NotificationsView.vue'),
                meta: { requiresTenantUser: true },
            },
            {
                path: 'notifications/settings',
                name: 'tenant.notifications.settings',
                component: () => import('@/views/tenant/NotificationSettingsView.vue'),
                meta: { requiresTenantUser: true },
            },
            {
                path: 'settings/profile',
                name: 'tenant.settings.profile',
                component: () => import('@/views/tenant/TenantProfileView.vue'),
                meta: { requiresTenantUser: true },
            },
            {
                path: 'settings/integrations',
                name: 'tenant.settings.integrations',
                component: () => import('@/views/tenant/IntegrationsView.vue'),
                meta: { requiresTenantUser: true },
            },
            {
                path: 'settings/activity',
                name: 'tenant.settings.activity',
                component: () => import('@/views/tenant/ActivityLogView.vue'),
                meta: { requiresTenantUser: true, right: 'activity_log.view' },
            },
            {
                path: 'settings/preferences',
                name: 'tenant.settings.preferences',
                component: () => import('@/views/tenant/PreferencesView.vue'),
                meta: { requiresTenantUser: true },
            },

            // ---------- Manager dashboard (users with the "manager" role) ----------
            {
                path: 'manager',
                name: 'manager.home',
                component: () => import('@/views/manager/ManagerHomeView.vue'),
                meta: { requiresManager: true, right: 'dashboard.view' },
            },
            {
                path: 'manager/team',
                name: 'manager.team',
                component: () => import('@/views/manager/ManagerTeamView.vue'),
                meta: { requiresManager: true, right: 'managers.view_profiles' },
            },
            {
                path: 'manager/assignments',
                name: 'manager.assignments',
                component: () => import('@/views/manager/ManagerAssignmentsView.vue'),
                meta: { requiresManager: true, right: 'chargebacks.assign' },
            },
            {
                path: 'manager/approvals',
                name: 'manager.approvals',
                component: () => import('@/views/manager/ManagerApprovalsView.vue'),
                meta: { requiresManager: true, right: 'chargebacks.stage_change' },
            },
            {
                path: 'manager/performance',
                name: 'manager.performance',
                component: () => import('@/views/tenant/ManagerProfilesView.vue'),
                meta: { requiresManager: true, right: 'dashboard.manager_performance' },
            },
            {
                path: 'manager/activity',
                name: 'manager.activity',
                component: () => import('@/views/manager/ManagerActivityView.vue'),
                meta: { requiresManager: true, right: 'activity_log.view_all' },
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

    if (to.meta.requiresTenantUser) {
        // Platform admins that aren't impersonating don't belong on tenant pages
        if (auth.user?.is_platform_admin && !auth.isImpersonating) {
            return { name: 'dashboard' };
        }
        const needed = to.meta.right as string | undefined;
        if (needed && !auth.can(needed)) {
            return { name: 'dashboard' };
        }
    }

    if (to.meta.requiresManager) {
        if (!auth.isManager) {
            return { name: 'dashboard' };
        }
        const needed = to.meta.right as string | undefined;
        if (needed && !auth.can(needed)) {
            return { name: 'dashboard' };
        }
    }
});

export default router;
