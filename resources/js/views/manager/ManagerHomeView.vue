<script setup lang="ts">
import api from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { computed, onMounted, ref } from 'vue';

interface ManagerHome {
    team?: { total?: number; active?: number; avg_score?: number | null };
    queues?: {
        unassigned_chargebacks?: number;
        unassigned_preventions?: number;
        unassigned_rdr?: number;
        approvals_pending?: number;
    };
    sla?: { at_risk?: number };
    win_rate?: number | null;
    recent_activity?: any[];
}

const auth = useAuthStore();
const home = ref<ManagerHome | null>(null);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/manager/home');
        home.value = data ?? null;
    } catch {
        home.value = null;
    } finally {
        loading.value = false;
    }
}

const kpis = computed(() => {
    const d = home.value ?? {};
    return [
        { label: 'Team members', value: d.team?.total, hint: `${d.team?.active ?? 0} active` },
        { label: 'Avg team score', value: d.team?.avg_score != null ? d.team?.avg_score : undefined, hint: 'Across managers' },
        { label: 'Unassigned chargebacks', value: d.queues?.unassigned_chargebacks, hint: 'Needs assignment' },
        { label: 'Unassigned preventions', value: d.queues?.unassigned_preventions, hint: 'Needs assignment' },
        { label: 'Unassigned RDR', value: d.queues?.unassigned_rdr, hint: 'Needs assignment' },
        { label: 'Pending approvals', value: d.queues?.approvals_pending, hint: 'Stage changes' },
        { label: 'SLA at risk', value: d.sla?.at_risk, hint: 'Nearing deadline' },
        { label: 'Win rate', value: d.win_rate != null ? `${d.win_rate}%` : undefined, hint: 'Team resolved' },
    ];
});

onMounted(load);
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0">
                <div class="font-semibold text-2xl">Manager dashboard — {{ auth.user?.name }}</div>
                <p class="text-muted-color m-0">Your team's workload, queues, and performance at a glance.</p>
            </div>
        </div>

        <div v-for="kpi in kpis" :key="kpi.label" class="col-span-6 md:col-span-4 xl:col-span-3">
            <div class="card !mb-0 flex flex-col gap-1">
                <span class="text-muted-color text-xs">{{ kpi.label }}</span>
                <span class="text-2xl font-semibold">{{ loading ? '—' : (kpi.value ?? '—') }}</span>
                <span v-if="kpi.hint" class="text-muted-color text-xs">{{ kpi.hint }}</span>
            </div>
        </div>

        <div class="col-span-12">
            <div class="card !mb-0">
                <div class="font-semibold mb-2">Recent team activity</div>
                <DataTable
                    :value="home?.recent_activity ?? []"
                    :loading="loading"
                    data-key="id"
                    striped-rows
                    size="small"
                >
                    <template #empty>No recent activity.</template>
                    <Column header="When">
                        <template #body="{ data: row }">
                            <span v-if="row.created_at" class="text-xs">{{ new Date(row.created_at).toLocaleString() }}</span>
                        </template>
                    </Column>
                    <Column header="User">
                        <template #body="{ data: row }">
                            <span v-if="row.user">{{ row.user.name }}</span>
                            <span v-else class="text-muted-color">—</span>
                        </template>
                    </Column>
                    <Column header="Action">
                        <template #body="{ data: row }">
                            <Tag :value="row.action" severity="secondary" class="font-mono" />
                        </template>
                    </Column>
                    <Column header="Target">
                        <template #body="{ data: row }">
                            <span class="text-xs text-muted-color">{{ row.target_type }} #{{ row.target_id }}</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>
