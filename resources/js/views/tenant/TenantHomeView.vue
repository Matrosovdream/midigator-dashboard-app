<script setup lang="ts">
import api from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { computed, onMounted, ref } from 'vue';

interface Summary {
    chargebacks?: { open?: number; total?: number; last_30d?: number; sla_at_risk?: number };
    preventions?: { open?: number; total?: number; last_30d?: number };
    rdr?: { open?: number; total?: number };
    orders?: { total?: number; last_30d?: number; pending?: number };
    validations?: { pending?: number };
    win_rate?: number;
    recent_activity?: any[];
}

const auth = useAuthStore();
const summary = ref<Summary | null>(null);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/dashboard/summary');
        summary.value = data ?? null;
    } catch {
        summary.value = null;
    } finally {
        loading.value = false;
    }
}

const kpis = computed(() => {
    const d = summary.value ?? {};
    return [
        { label: 'Open chargebacks', value: d.chargebacks?.open, hint: `${d.chargebacks?.last_30d ?? 0} in last 30d` },
        { label: 'Pending preventions', value: d.preventions?.open, hint: `${d.preventions?.last_30d ?? 0} in last 30d` },
        { label: 'Open RDR', value: d.rdr?.open, hint: `${d.rdr?.total ?? 0} all time` },
        { label: 'Pending orders', value: d.orders?.pending, hint: `${d.orders?.last_30d ?? 0} in last 30d` },
        { label: 'Validations to review', value: d.validations?.pending, hint: '' },
        { label: 'SLA at risk', value: d.chargebacks?.sla_at_risk, hint: 'Chargebacks nearing deadline' },
        { label: 'Win rate', value: d.win_rate != null ? `${d.win_rate}%` : undefined, hint: 'Resolved in your favor' },
    ];
});

onMounted(load);
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0">
                <div class="font-semibold text-2xl">Welcome back, {{ auth.user?.name }} 👋</div>
                <p class="text-muted-color m-0">Here's a snapshot of your cases and workload.</p>
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
                <div class="font-semibold mb-2">Recent activity</div>
                <DataTable
                    :value="summary?.recent_activity ?? []"
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
