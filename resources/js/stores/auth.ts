import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import api, { ensureCsrf } from '@/lib/api';

export interface AuthUser {
    id: number;
    tenant_id: number | null;
    email: string;
    name: string;
    avatar: string | null;
    is_platform_admin: boolean;
    roles: { id: number; name: string; slug: string }[];
    rights: string[];
}

export interface Impersonator {
    id: number;
    name: string;
    email: string;
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(null);
    const impersonator = ref<Impersonator | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const isAuthenticated = computed(() => user.value !== null);
    const isImpersonating = computed(() => impersonator.value !== null);

    const isPlatformAdmin = computed(() => !!user.value?.is_platform_admin && !isImpersonating.value);

    function hasRole(slug: string): boolean {
        return !!user.value?.roles?.some((r) => r.slug === slug);
    }

    const isManager = computed(() => {
        if (!user.value || isPlatformAdmin.value) return false;
        return hasRole('manager') && !hasRole('tenant-admin');
    });

    const persona = computed<'platform' | 'manager' | 'tenant'>(() => {
        if (isPlatformAdmin.value) return 'platform';
        if (isManager.value) return 'manager';
        return 'tenant';
    });

    function can(right: string): boolean {
        if (!user.value) return false;
        if (user.value.is_platform_admin) return true;
        return user.value.rights.includes(right);
    }

    async function login(email: string, password: string): Promise<void> {
        loading.value = true;
        error.value = null;
        try {
            await ensureCsrf();
            const { data } = await api.post('/api/v1/auth/login', { email, password });
            user.value = data.user as AuthUser;
            impersonator.value = null;
        } catch (e: any) {
            error.value = e?.response?.data?.message ?? 'Login failed';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function loginWithPin(pin: string): Promise<void> {
        loading.value = true;
        error.value = null;
        try {
            await ensureCsrf();
            const { data } = await api.post('/api/v1/auth/login-pin', { pin });
            user.value = data.user as AuthUser;
            impersonator.value = null;
        } catch (e: any) {
            error.value = e?.response?.data?.message ?? 'Login failed';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function fetchMe(): Promise<void> {
        try {
            const { data } = await api.get('/api/v1/auth/me');
            user.value = data.user as AuthUser;
            impersonator.value = (data.impersonator as Impersonator | null) ?? null;
        } catch {
            user.value = null;
            impersonator.value = null;
        }
    }

    async function logout(): Promise<void> {
        try {
            await api.post('/api/v1/auth/logout');
        } finally {
            user.value = null;
            impersonator.value = null;
        }
    }

    async function startImpersonation(userId: number): Promise<void> {
        await ensureCsrf();
        const { data } = await api.post(`/api/v1/platform/impersonate/${userId}`);
        user.value = data.user as AuthUser;
        impersonator.value = (data.impersonator as Impersonator | null) ?? null;
    }

    async function stopImpersonation(): Promise<void> {
        const { data } = await api.post('/api/v1/platform/impersonate/stop');
        user.value = data.user as AuthUser;
        impersonator.value = null;
    }

    return {
        user,
        impersonator,
        loading,
        error,
        isAuthenticated,
        isImpersonating,
        isPlatformAdmin,
        isManager,
        persona,
        hasRole,
        can,
        login,
        loginWithPin,
        fetchMe,
        logout,
        startImpersonation,
        stopImpersonation,
    };
});
