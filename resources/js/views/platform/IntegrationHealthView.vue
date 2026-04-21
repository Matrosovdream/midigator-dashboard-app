<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

interface HealthRow {
    tenant_id: number;
    tenant_name: string;
    tenant_slug: string;
    is_active: boolean;
    sandbox_mode: boolean;
    has_api_secret: boolean;
    total: number;
    failed: number;
    failure_rate: number;
    last_success_at: string | null;
    last_failure_at: string | null;
    status: 'red' | 'amber' | 'green' | 'grey';
}

const router = useRouter();

const rows = ref<HealthRow[]>([]);
const loading = ref(false);
const windowHours = ref(24);

const windowOptions = [
    { label: 'Last 1 h', value: 1 },
    { label: 'Last 24 h', value: 24 },
    { label: 'Last 7 days', value: 168 },
];

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/integrations/health', {
            params: { window_hours: windowHours.value },
        });
        rows.value = data.rows ?? [];
    } finally {
        loading.value = false;
    }
}

function tagSeverity(status: HealthRow['status']): 'success' | 'warn' | 'danger' | 'secondary' {
    if (status === 'green') return 'success';
    if (status === 'amber') return 'warn';
    if (status === 'red') return 'danger';
    return 'secondary';
}

function tagLabel(status: HealthRow['status']): string {
    if (status === 'green') return 'Healthy';
    if (status === 'amber') return 'Degraded';
    if (status === 'red') return 'Broken';
    return 'Idle';
}

function onRowClick(event: { data: HealthRow }) {
    router.push({ name: 'platform.tenants.show', params: { id: event.data.tenant_id } });
}
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Integration Health</div>
                <p class="text-muted-color m-0">Midigator webhook status per tenant. Click a row for details.</p>
            </div>
            <div class="flex gap-2 items-center">
                <Select
                    v-model="windowHours"
                    :options="windowOptions"
                    option-label="label"
                    option-value="value"
                    class="w-40"
                    @change="load"
                />
                <Button icon="pi pi-refresh" outlined aria-label="Reload" @click="load" />
            </div>
        </div>

        <DataTable
            :value="rows"
            :loading="loading"
            data-key="tenant_id"
            striped-rows
            row-hover
            @row-click="onRowClick"
        >
            <template #empty>No tenants.</template>

            <Column header="Tenant">
                <template #body="{ data }">
                    <div class="font-medium">{{ data.tenant_name }}</div>
                    <div class="text-muted-color text-xs">{{ data.tenant_slug }}</div>
                </template>
            </Column>

            <Column header="Env" style="width: 7rem">
                <template #body="{ data }">
                    <Tag
                        :value="data.sandbox_mode ? 'Sandbox' : 'Live'"
                        :severity="data.sandbox_mode ? 'warn' : 'success'"
                    />
                </template>
            </Column>

            <Column header="Status" style="width: 8rem">
                <template #body="{ data }">
                    <Tag :value="tagLabel(data.status)" :severity="tagSeverity(data.status)" />
                </template>
            </Column>

            <Column header="Webhooks (window)" style="width: 10rem">
                <template #body="{ data }">
                    <span class="font-semibold">{{ data.total }}</span>
                    <span v-if="data.failed > 0" class="text-red-500 text-xs ml-1">({{ data.failed }} failed)</span>
                </template>
            </Column>

            <Column header="Failure rate" style="width: 8rem">
                <template #body="{ data }">
                    <span :class="{ 'text-red-500 font-semibold': data.failure_rate >= 25 }">
                        {{ data.failure_rate }}%
                    </span>
                </template>
            </Column>

            <Column header="Last success" style="width: 12rem">
                <template #body="{ data }">
                    <span v-if="data.last_success_at" class="text-xs">{{ new Date(data.last_success_at).toLocaleString() }}</span>
                    <span v-else class="text-muted-color text-xs">—</span>
                </template>
            </Column>

            <Column header="Last failure" style="width: 12rem">
                <template #body="{ data }">
                    <span v-if="data.last_failure_at" class="text-xs text-red-500">{{ new Date(data.last_failure_at).toLocaleString() }}</span>
                    <span v-else class="text-muted-color text-xs">—</span>
                </template>
            </Column>

            <Column header="Credentials" style="width: 9rem">
                <template #body="{ data }">
                    <Tag
                        v-if="!data.has_api_secret"
                        value="No API secret"
                        severity="danger"
                    />
                    <Tag v-else-if="!data.is_active" value="Suspended" severity="secondary" />
                    <Tag v-else value="OK" severity="success" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
