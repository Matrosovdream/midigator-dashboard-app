<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useLayout } from '@/layout/composables/layout';
import { useAuthStore } from '@/stores/auth';
import AppConfigurator from './AppConfigurator.vue';

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();
const auth = useAuthStore();
const router = useRouter();

const userMenu = ref(null);
const userInitials = computed(() => {
    const name = auth.user?.name ?? 'U';
    return name
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const userMenuItems = computed(() => [
    {
        label: auth.user?.email ?? '',
        disabled: true,
    },
    { separator: true },
    {
        label: 'Sign out',
        icon: 'pi pi-sign-out',
        command: async () => {
            await auth.logout();
            router.push({ name: 'login' });
        },
    },
]);

function toggleUserMenu(event) {
    userMenu.value.toggle(event);
}
</script>

<template>
    <div class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button class="layout-menu-button layout-topbar-action" @click="toggleMenu">
                <i class="pi pi-bars"></i>
            </button>
            <router-link to="/dashboard" class="layout-topbar-logo">
                <i class="pi pi-shield" style="color: var(--primary-color); font-size: 1.5rem"></i>
                <span>MIDIGATOR</span>
            </router-link>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
                    <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
                </button>
                <div class="relative">
                    <button
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'p-anchored-overlay-enter-active', leaveToClass: 'hidden', leaveActiveClass: 'p-anchored-overlay-leave-active', hideOnOutsideClick: true }"
                        type="button"
                        class="layout-topbar-action layout-topbar-action-highlight"
                    >
                        <i class="pi pi-palette"></i>
                    </button>
                    <AppConfigurator />
                </div>
            </div>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <button type="button" class="layout-topbar-action" @click="toggleUserMenu" aria-haspopup="true">
                        <Avatar :label="userInitials" shape="circle" size="normal" />
                        <span>{{ auth.user?.name ?? 'Account' }}</span>
                    </button>
                    <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
                </div>
            </div>

            <button type="button" class="layout-topbar-menu-button layout-topbar-action lg:hidden" @click="toggleUserMenu" aria-haspopup="true">
                <i class="pi pi-user"></i>
            </button>
        </div>
    </div>
</template>
