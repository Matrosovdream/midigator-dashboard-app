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

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const isAuthenticated = computed(() => user.value !== null);

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
        } catch {
            user.value = null;
        }
    }

    async function logout(): Promise<void> {
        try {
            await api.post('/api/v1/auth/logout');
        } finally {
            user.value = null;
        }
    }

    return { user, loading, error, isAuthenticated, can, login, fetchMe, logout };
});
