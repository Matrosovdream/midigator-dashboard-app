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
});

export default router;
