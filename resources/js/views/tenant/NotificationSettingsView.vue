<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Card from 'primevue/card';
import ToggleSwitch from 'primevue/toggleswitch';
import { onMounted, ref } from 'vue';

const settings = ref<Record<string, { email: boolean; in_app: boolean }>>({});
const loading = ref(false);
const saving = ref(false);

const channels: { key: string; label: string; description: string }[] = [
    { key: 'chargeback.new', label: 'New chargeback', description: 'A new dispute has been received.' },
    { key: 'chargeback.deadline', label: 'Chargeback deadline', description: 'A chargeback is approaching its response deadline.' },
    { key: 'prevention.new', label: 'New prevention alert', description: 'A new Ethoca/Verifi alert.' },
    { key: 'rdr.new', label: 'New RDR case', description: 'A new RDR case has been opened.' },
    { key: 'assignment', label: 'Assigned to me', description: 'A case is assigned to you.' },
    { key: 'mention', label: 'Comment mentions', description: 'Someone @mentions you in a comment.' },
];

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/notification-settings');
        settings.value = data.settings ?? {};
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    try {
        await api.patch('/api/v1/notification-settings', { settings: settings.value });
    } finally {
        saving.value = false;
    }
}

function get(key: string, ch: 'email' | 'in_app'): boolean {
    return settings.value[key]?.[ch] ?? true;
}

function set(key: string, ch: 'email' | 'in_app', v: boolean) {
    if (!settings.value[key]) settings.value[key] = { email: true, in_app: true };
    settings.value[key][ch] = v;
}

onMounted(load);
</script>

<template>
    <Card>
        <template #title>Notification preferences</template>
        <template #content>
            <div class="flex flex-col gap-4">
                <div v-for="ch in channels" :key="ch.key" class="flex items-center justify-between py-2 border-b border-surface-200 dark:border-surface-700 last:border-0">
                    <div>
                        <div class="font-medium">{{ ch.label }}</div>
                        <div class="text-sm text-muted-color">{{ ch.description }}</div>
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm">
                            <ToggleSwitch :model-value="get(ch.key, 'in_app')" @update:model-value="(v: any) => set(ch.key, 'in_app', v)" />
                            In-app
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <ToggleSwitch :model-value="get(ch.key, 'email')" @update:model-value="(v: any) => set(ch.key, 'email', v)" />
                            Email
                        </label>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <Button label="Save" icon="pi pi-save" :loading="saving" @click="save" />
            </div>
        </template>
    </Card>
</template>
