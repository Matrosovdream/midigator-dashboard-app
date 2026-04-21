<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import Button from 'primevue/button';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();
const stopping = ref(false);

async function stop() {
    stopping.value = true;
    try {
        await auth.stopImpersonation();
        router.push({ name: 'platform.tenants.index' });
    } finally {
        stopping.value = false;
    }
}
</script>

<template>
    <div
        v-if="auth.isImpersonating"
        class="flex items-center justify-between gap-3 px-4 py-2 bg-yellow-100 text-yellow-900 border-b border-yellow-300 dark:bg-yellow-900/40 dark:text-yellow-100 dark:border-yellow-800"
    >
        <div class="flex items-center gap-2 text-sm">
            <i class="pi pi-eye" />
            <span>
                You are impersonating
                <strong>{{ auth.user?.name }}</strong>
                (<span class="font-mono">{{ auth.user?.email }}</span>) as
                <strong>{{ auth.impersonator?.name }}</strong>.
            </span>
        </div>
        <Button
            label="Stop impersonating"
            icon="pi pi-sign-out"
            severity="warn"
            size="small"
            :loading="stopping"
            @click="stop"
        />
    </div>
</template>
