<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

interface WebhookRow {
    id: number;
    tenant_id: number | null;
    tenant: { id: number; name: string } | null;
    event_type: string | null;
    event_guid: string | null;
    status: string | null;
    error_message: string | null;
    processed_at: string | null;
    created_at: string | null;
}

interface TenantOption { id: number; name: string }

const rows = ref<WebhookRow[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(25);

const tenantFilter = ref<number | null>(null);
const statusFilter = ref<string | null>(null);
const eventTypeFilter = ref('');

const tenantOptions = ref<TenantOption[]>([]);

const statusOptions = [
    { label: 'Any', value: null },
    { label: 'Processed', value: 'processed' },
    { label: 'Failed', value: 'failed' },
    { label: 'Pending', value: 'pending' },
];

const detail = ref<any | null>(null);
const detailLoading = ref(false);
const detailOpen = ref(false);

async function loadTenantOptions() {
    const { data } = await api.get('/api/v1/platform/tenants', { params: { per_page: 200 } });
    tenantOptions.value = (data.items ?? []).map((t: any) => ({ id: t.id, name: t.name }));
}

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/webhooks', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                tenant_id: tenantFilter.value ?? undefined,
                status: statusFilter.value ?? undefined,
                event_type: eventTypeFilter.value || undefined,
            },
        });
        rows.value = data.items ?? [];
        totalRecords.value = data.Model?.total ?? rows.value.length;
    } finally {
        loading.value = false;
    }
}

function onPage(event: DataTablePageEvent) {
    page.value = event.page;
    perPage.value = event.rows;
    load();
}

function resetAndLoad() {
    page.value = 0;
    load();
}

async function openDetail(id: number) {
    detailOpen.value = true;
    detail.value = null;
    detailLoading.value = true;
    try {
        const { data } = await api.get(`/api/v1/platform/webhooks/${id}`);
        detail.value = data.webhook;
    } finally {
        detailLoading.value = false;
    }
}

function severityFor(status: string | null): 'success' | 'warn' | 'danger' | 'secondary' {
    if (!status) return 'secondary';
    if (status === 'processed') return 'success';
    if (status === 'failed') return 'danger';
    return 'warn';
}

onMounted(() => {
    loadTenantOptions();
    load();
});
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Webhook Logs</div>
                <p class="text-muted-color m-0">Every Midigator webhook received across the platform.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <Select
                v-model="tenantFilter"
                :options="[{ id: null, name: 'Any tenant' }, ...tenantOptions]"
                option-label="name"
                option-value="id"
                placeholder="Tenant"
                filter
                class="w-full md:w-56"
                @change="resetAndLoad"
            />
            <Select
                v-model="statusFilter"
                :options="statusOptions"
                option-label="label"
                option-value="value"
                placeholder="Status"
                class="w-full md:w-40"
                @change="resetAndLoad"
            />
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="eventTypeFilter"
                    placeholder="Event type (e.g. chargeback.created)"
                    class="w-full"
                    @keyup.enter="resetAndLoad"
                />
            </IconField>
            <Button icon="pi pi-search" label="Search" @click="resetAndLoad" />
        </div>

        <DataTable
            :value="rows"
            :loading="loading"
            lazy
            paginator
            :rows="perPage"
            :rows-per-page-options="[25, 50, 100, 200]"
            :total-records="totalRecords"
            :first="page * perPage"
            data-key="id"
            striped-rows
            row-hover
            @page="onPage"
            @row-click="(e: any) => openDetail(e.data.id)"
        >
            <template #empty>No webhooks.</template>

            <Column header="Received" style="width: 13rem">
                <template #body="{ data }">
                    <span v-if="data.created_at" class="text-xs">{{ new Date(data.created_at).toLocaleString() }}</span>
                </template>
            </Column>

            <Column header="Tenant">
                <template #body="{ data }">
                    <span v-if="data.tenant">{{ data.tenant.name }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column field="event_type" header="Event">
                <template #body="{ data }">
                    <span class="font-mono text-xs">{{ data.event_type ?? '—' }}</span>
                </template>
            </Column>

            <Column header="Status" style="width: 8rem">
                <template #body="{ data }">
                    <Tag :value="data.status ?? '—'" :severity="severityFor(data.status)" />
                </template>
            </Column>

            <Column header="Error">
                <template #body="{ data }">
                    <span v-if="data.error_message" class="text-red-500 text-xs line-clamp-2">{{ data.error_message }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="detailOpen"
            header="Webhook detail"
            modal
            :style="{ width: 'min(900px, 95vw)' }"
        >
            <div v-if="detailLoading" class="text-muted-color">Loading…</div>
            <div v-else-if="detail" class="flex flex-col gap-3">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-muted-color">Tenant:</span> {{ detail.tenant?.name ?? '—' }}</div>
                    <div><span class="text-muted-color">Status:</span>
                        <Tag :value="detail.status ?? '—'" :severity="severityFor(detail.status)" class="ml-2" />
                    </div>
                    <div><span class="text-muted-color">Event:</span>
                        <span class="font-mono">{{ detail.event_type ?? '—' }}</span>
                    </div>
                    <div><span class="text-muted-color">Received:</span>
                        {{ detail.created_at ? new Date(detail.created_at).toLocaleString() : '—' }}
                    </div>
                    <div class="col-span-2">
                        <span class="text-muted-color">Event GUID:</span>
                        <span class="font-mono text-xs">{{ detail.event_guid ?? '—' }}</span>
                    </div>
                </div>
                <div v-if="detail.error_message">
                    <div class="text-muted-color text-sm mb-1">Error</div>
                    <pre class="text-xs text-red-500 bg-surface-100 dark:bg-surface-800 p-2 rounded overflow-auto">{{ detail.error_message }}</pre>
                </div>
                <div>
                    <div class="text-muted-color text-sm mb-1">Payload</div>
                    <pre class="text-xs bg-surface-100 dark:bg-surface-800 p-2 rounded overflow-auto max-h-96">{{ JSON.stringify(detail.payload, null, 2) }}</pre>
                </div>
            </div>
        </Dialog>
    </div>
</template>
