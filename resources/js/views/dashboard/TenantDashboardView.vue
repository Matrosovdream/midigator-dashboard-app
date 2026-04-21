<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const userName = computed(() => auth.user?.name ?? 'there');
</script>

<template>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <div class="card">
                <div class="font-semibold text-2xl mb-2">Welcome back, {{ userName }} 👋</div>
                <p class="text-muted-color">This is the Midigator dashboard. Widgets and stats will appear here.</p>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-6 xl:col-span-3">
            <div class="card flex flex-col gap-2">
                <span class="block text-muted-color font-medium">Open Chargebacks</span>
                <div class="text-surface-900 dark:text-surface-0 font-semibold text-3xl">—</div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6 xl:col-span-3">
            <div class="card flex flex-col gap-2">
                <span class="block text-muted-color font-medium">Active Alerts</span>
                <div class="text-surface-900 dark:text-surface-0 font-semibold text-3xl">—</div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6 xl:col-span-3">
            <div class="card flex flex-col gap-2">
                <span class="block text-muted-color font-medium">Open RDR Cases</span>
                <div class="text-surface-900 dark:text-surface-0 font-semibold text-3xl">—</div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6 xl:col-span-3">
            <div class="card flex flex-col gap-2">
                <span class="block text-muted-color font-medium">Pending Orders</span>
                <div class="text-surface-900 dark:text-surface-0 font-semibold text-3xl">—</div>
            </div>
        </div>
    </div>
</template>
