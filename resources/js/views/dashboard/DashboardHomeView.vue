<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const PlatformOverviewView = defineAsyncComponent(() => import('@/views/platform/PlatformOverviewView.vue'));
const TenantDashboardView = defineAsyncComponent(() => import('@/views/tenant/TenantHomeView.vue'));

const isPlatformAdmin = computed(() => !!auth.user?.is_platform_admin && !auth.isImpersonating);
</script>

<template>
    <PlatformOverviewView v-if="isPlatformAdmin" />
    <TenantDashboardView v-else />
</template>
