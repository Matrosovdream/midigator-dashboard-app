<script setup lang="ts">
import api from '@/lib/api';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputNumber from 'primevue/inputnumber';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/dashboard/manager-performance');
        rows.value = data.items ?? [];
    } catch {
        rows.value = [];
    } finally {
        loading.value = false;
    }
}

async function saveScore(userId: number, score: number) {
    await api.patch(`/api/v1/users/${userId}/score`, { score });
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">Manager Profiles</div>
        <p class="text-muted-color mb-4">Performance, workload, and score per manager.</p>

        <DataTable :value="rows" :loading="loading" data-key="user_id" striped-rows>
            <template #empty>No managers yet.</template>
            <Column field="name" header="Name" />
            <Column field="open_cases" header="Open cases" style="width: 10rem" />
            <Column field="resolved_30d" header="Resolved 30d" style="width: 10rem" />
            <Column field="win_rate" header="Win rate" style="width: 10rem">
                <template #body="{ data }">
                    <span v-if="data.win_rate != null">{{ data.win_rate }}%</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="Score" style="width: 12rem">
                <template #body="{ data }">
                    <InputNumber v-model="data.score" :min="0" :max="100" :step="1" show-buttons size="small" @blur="saveScore(data.user_id, data.score)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
