<script setup lang="ts">
import api from '@/lib/api';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/manager/activity');
        rows.value = data.items ?? [];
    } catch {
        rows.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">Team activity</div>
        <p class="text-muted-color mb-4">Everything your team has done recently.</p>

        <DataTable :value="rows" :loading="loading" data-key="id" striped-rows size="small">
            <template #empty>No activity.</template>
            <Column header="When">
                <template #body="{ data }">
                    <span v-if="data.created_at" class="text-xs">{{ new Date(data.created_at).toLocaleString() }}</span>
                </template>
            </Column>
            <Column header="User">
                <template #body="{ data }">
                    <span v-if="data.user">{{ data.user.name }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="Action">
                <template #body="{ data }">
                    <Tag :value="data.action" severity="secondary" class="font-mono" />
                </template>
            </Column>
            <Column header="Target">
                <template #body="{ data }">
                    <span class="text-xs text-muted-color">{{ data.target_type }} #{{ data.target_id }}</span>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
