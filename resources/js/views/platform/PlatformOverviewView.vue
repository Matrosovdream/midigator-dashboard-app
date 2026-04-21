<script setup lang="ts">
import api from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { computed, onMounted, ref } from 'vue';

interface Summary {
    tenants: { total: number; active: number; suspended: number };
    users: { total: number; active: number; active_last_7d: number };
    chargebacks: { total: number; last_30d: number };
    preventions: { total: number; last_30d: number };
    orders: { total: number; last_30d: number };
    rdr: { total: number };
    webhooks_24h: { total: number; failed: number };
    recent_failed_webhooks: any[];
    recent_events: any[];
}

const auth = useAuthStore();
const data = ref<Summary | null>(null);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v1/platform/overview/summary');
        data.value = res.data;
    } finally {
        loading.value = false;
    }
}

const kpis = computed(() => {
    if (!data.value) return [];
    const d = data.value;
    return [
        { label: 'Active tenants', value: d.tenants.active, hint: `${d.tenants.total} total, ${d.tenants.suspended} suspended` },
        { label: 'Active users (7d)', value: d.users.active_last_7d, hint: `${d.users.active} active, ${d.users.total} total` },
        { label: 'Chargebacks (30d)', value: d.chargebacks.last_30d, hint: `${d.chargebacks.total} all time` },
        { label: 'Preventions (30d)', value: d.preventions.last_30d, hint: `${d.preventions.total} all time` },
        { label: 'Orders (30d)', value: d.orders.last_30d, hint: `${d.orders.total} all time` },
        { label: 'RDR cases', value: d.rdr.total, hint: '' },
        { label: 'Webhooks 24h', value: d.webhooks_24h.total, hint: `${d.webhooks_24h.failed} failed` },
    ];
});

function severityFor(status: string | null): 'success' | 'warn' | 'danger' | 'secondary' {
    if (!status) return 'secondary';
    if (status === 'processed') return 'success';
    if (status === 'failed') return 'danger';
    return 'warn';
}

onMounted(load);
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0">
                <div class="font-semibold text-2xl">Platform overview</div>
                <p class="text-muted-color m-0">
                    Welcome back, {{ auth.user?.name }}. Here's what's happening across all tenants.
                </p>
            </div>
        </div>

        <div v-for="kpi in kpis" :key="kpi.label" class="col-span-6 md:col-span-4 xl:col-span-3">
            <div class="card !mb-0 flex flex-col gap-1">
                <span class="text-muted-color text-xs">{{ kpi.label }}</span>
                <span class="text-2xl font-semibold">{{ loading ? '—' : kpi.value }}</span>
                <span v-if="kpi.hint" class="text-muted-color text-xs">{{ kpi.hint }}</span>
            </div>
        </div>

        <!-- Recent failed webhooks -->
        <div class="col-span-12 xl:col-span-6">
            <div class="card !mb-0">
                <div class="font-semibold mb-2">Recent failed webhooks</div>
                <DataTable
                    :value="data?.recent_failed_webhooks ?? []"
                    :loading="loading"
                    data-key="id"
                    striped-rows
                    size="small"
                >
                    <template #empty>No failures recorded.</template>
                    <Column header="When">
                        <template #body="{ data: row }">
                            <span v-if="row.created_at" class="text-xs">{{ new Date(row.created_at).toLocaleString() }}</span>
                        </template>
                    </Column>
                    <Column header="Tenant">
                        <template #body="{ data: row }">
                            <span v-if="row.tenant">{{ row.tenant.name }}</span>
                            <span v-else class="text-muted-color">—</span>
                        </template>
                    </Column>
                    <Column field="event_type" header="Event">
                        <template #body="{ data: row }">
                            <span class="font-mono text-xs">{{ row.event_type ?? '—' }}</span>
                        </template>
                    </Column>
                    <Column field="error_message" header="Error">
                        <template #body="{ data: row }">
                            <span v-if="row.error_message" class="text-red-500 text-xs">{{ row.error_message }}</span>
                            <span v-else class="text-muted-color">—</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Recent platform events -->
        <div class="col-span-12 xl:col-span-6">
            <div class="card !mb-0">
                <div class="font-semibold mb-2">Recent activity</div>
                <DataTable
                    :value="data?.recent_events ?? []"
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
                    <Column header="Tenant">
                        <template #body="{ data: row }">
                            <span v-if="row.tenant">{{ row.tenant.name }}</span>
                            <span v-else class="text-muted-color">—</span>
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
                            <Tag :value="row.action" :severity="severityFor(null)" class="font-mono" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>
