<script setup lang="ts">
import api from '@/lib/api';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

interface TeamRow {
    user_id: number;
    name: string;
    email?: string;
    is_active?: boolean;
    score?: number | null;
    open_cases?: number;
    resolved_30d?: number;
    win_rate?: number | null;
    last_login_at?: string | null;
}

const rows = ref<TeamRow[]>([]);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/manager/team');
        rows.value = (data.items ?? []) as TeamRow[];
    } catch {
        rows.value = [];
    } finally {
        loading.value = false;
    }
}

async function saveScore(userId: number, score: number | null | undefined) {
    if (score == null) return;
    await api.patch(`/api/v1/users/${userId}/score`, { score });
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">My team</div>
        <p class="text-muted-color mb-4">Workload, win rate, and score for each team member.</p>

        <DataTable :value="rows" :loading="loading" data-key="user_id" striped-rows>
            <template #empty>No team members yet.</template>
            <Column field="name" header="Name" />
            <Column header="Status" style="width: 8rem">
                <template #body="{ data }">
                    <Tag :value="data.is_active ? 'Active' : 'Inactive'" :severity="data.is_active ? 'success' : 'secondary'" />
                </template>
            </Column>
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
                    <InputNumber
                        v-model="data.score"
                        :min="0"
                        :max="100"
                        :step="1"
                        show-buttons
                        size="small"
                        @blur="saveScore(data.user_id, data.score)"
                    />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
