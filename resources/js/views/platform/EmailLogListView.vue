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

interface EmailRow {
    id: number;
    tenant_id: number | null;
    tenant: { id: number; name: string } | null;
    user: { id: number; name: string } | null;
    to_email: string;
    subject: string;
    status: string;
    sent_at: string | null;
    template: { id: number; name: string } | null;
    created_at: string | null;
}

interface TenantOption { id: number; name: string }

const rows = ref<EmailRow[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(25);

const search = ref('');
const tenantFilter = ref<number | null>(null);
const statusFilter = ref<string | null>(null);

const tenantOptions = ref<TenantOption[]>([]);

const statusOptions = [
    { label: 'Any', value: null },
    { label: 'Queued', value: 'queued' },
    { label: 'Sent', value: 'sent' },
    { label: 'Delivered', value: 'delivered' },
    { label: 'Opened', value: 'opened' },
    { label: 'Failed', value: 'failed' },
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
        const { data } = await api.get('/api/v1/platform/emails/logs', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: search.value || undefined,
                tenant_id: tenantFilter.value ?? undefined,
                status: statusFilter.value ?? undefined,
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
        const { data } = await api.get(`/api/v1/platform/emails/logs/${id}`);
        detail.value = data.email;
    } finally {
        detailLoading.value = false;
    }
}

function severityFor(status: string | null): 'success' | 'warn' | 'danger' | 'info' | 'secondary' {
    if (!status) return 'secondary';
    if (status === 'delivered' || status === 'opened') return 'success';
    if (status === 'sent') return 'info';
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
                <div class="font-semibold text-2xl">Email Logs</div>
                <p class="text-muted-color m-0">All emails dispatched across the platform.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="search"
                    placeholder="Search recipient or subject"
                    class="w-full"
                    @keyup.enter="resetAndLoad"
                />
            </IconField>
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
            <template #empty>No emails.</template>

            <Column header="Sent" style="width: 13rem">
                <template #body="{ data }">
                    <span v-if="data.sent_at" class="text-xs">{{ new Date(data.sent_at).toLocaleString() }}</span>
                    <span v-else-if="data.created_at" class="text-xs text-muted-color">{{ new Date(data.created_at).toLocaleString() }}</span>
                </template>
            </Column>

            <Column header="Tenant">
                <template #body="{ data }">
                    <span v-if="data.tenant">{{ data.tenant.name }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column field="to_email" header="To" />
            <Column field="subject" header="Subject" />

            <Column header="Template">
                <template #body="{ data }">
                    <span v-if="data.template" class="font-mono text-xs">{{ data.template.name }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column header="Status" style="width: 8rem">
                <template #body="{ data }">
                    <Tag :value="data.status" :severity="severityFor(data.status)" />
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="detailOpen"
            header="Email detail"
            modal
            :style="{ width: 'min(900px, 95vw)' }"
        >
            <div v-if="detailLoading" class="text-muted-color">Loading…</div>
            <div v-else-if="detail" class="flex flex-col gap-3 text-sm">
                <div class="grid grid-cols-2 gap-3">
                    <div><span class="text-muted-color">Tenant:</span> {{ detail.tenant?.name ?? '—' }}</div>
                    <div><span class="text-muted-color">Status:</span>
                        <Tag :value="detail.status" :severity="severityFor(detail.status)" class="ml-2" />
                    </div>
                    <div><span class="text-muted-color">To:</span> {{ detail.to_email }}</div>
                    <div><span class="text-muted-color">Sender:</span> {{ detail.user?.name ?? 'system' }}</div>
                    <div class="col-span-2"><span class="text-muted-color">Subject:</span> {{ detail.subject }}</div>
                </div>
                <div>
                    <div class="text-muted-color text-sm mb-1">Body</div>
                    <iframe
                        v-if="detail.body"
                        :srcdoc="detail.body"
                        class="w-full border border-surface-200 dark:border-surface-700 rounded"
                        style="height: 400px"
                    ></iframe>
                </div>
            </div>
        </Dialog>
    </div>
</template>
