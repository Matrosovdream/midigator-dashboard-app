<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const PlatformOverviewView = defineAsyncComponent(() => import('@/views/platform/PlatformOverviewView.vue'));
const ManagerHomeView = defineAsyncComponent(() => import('@/views/manager/ManagerHomeView.vue'));
const TenantDashboardView = defineAsyncComponent(() => import('@/views/tenant/TenantHomeView.vue'));

const persona = computed(() => auth.persona);
</script>

<template>
    <PlatformOverviewView v-if="persona === 'platform'" />
    <ManagerHomeView v-else-if="persona === 'manager'" />
    <TenantDashboardView v-else />
</template>
