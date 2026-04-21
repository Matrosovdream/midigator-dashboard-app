<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DatePicker from 'primevue/datepicker';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

interface ActivityRow {
    id: number;
    tenant_id: number;
    tenant: { id: number; name: string } | null;
    user_id: number | null;
    user: { id: number; name: string } | null;
    loggable_type: string | null;
    loggable_id: number | null;
    action: string;
    metadata: Record<string, any>;
    created_at: string | null;
}

interface TenantOption { id: number; name: string }

const rows = ref<ActivityRow[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(50);

const search = ref('');
const tenantFilter = ref<number | null>(null);
const dateRange = ref<Date[] | null>(null);

const tenantOptions = ref<TenantOption[]>([]);

const detail = ref<ActivityRow | null>(null);
const detailOpen = ref(false);

async function loadTenantOptions() {
    const { data } = await api.get('/api/v1/platform/tenants', { params: { per_page: 200 } });
    tenantOptions.value = (data.items ?? []).map((t: any) => ({ id: t.id, name: t.name }));
}

function formatDate(d?: Date | null): string | undefined {
    if (!d) return undefined;
    return d.toISOString().slice(0, 10);
}

async function load() {
    loading.value = true;
    try {
        const [from, to] = dateRange.value ?? [];
        const { data } = await api.get('/api/v1/platform/activity', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: search.value || undefined,
                tenant_id: tenantFilter.value ?? undefined,
                from: formatDate(from),
                to: formatDate(to),
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

function openDetail(row: ActivityRow) {
    detail.value = row;
    detailOpen.value = true;
}

function shortType(t: string | null): string {
    if (!t) return '';
    return t.split('\\').pop() ?? t;
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
                <div class="font-semibold text-2xl">Global Activity Log</div>
                <p class="text-muted-color m-0">Every action recorded across all tenants.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="search"
                    placeholder="Search action or target type"
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
            <DatePicker
                v-model="dateRange"
                selection-mode="range"
                placeholder="Date range"
                show-icon
                :manual-input="false"
                class="w-full md:w-72"
                @date-select="resetAndLoad"
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
            @row-click="(e: any) => openDetail(e.data)"
        >
            <template #empty>No activity recorded.</template>

            <Column header="When" style="width: 13rem">
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

            <Column header="User">
                <template #body="{ data }">
                    <span v-if="data.user">{{ data.user.name }}</span>
                    <span v-else class="text-muted-color">system</span>
                </template>
            </Column>

            <Column header="Action">
                <template #body="{ data }">
                    <Tag :value="data.action" severity="secondary" class="font-mono" />
                </template>
            </Column>

            <Column header="Target">
                <template #body="{ data }">
                    <span v-if="data.loggable_type" class="font-mono text-xs">
                        {{ shortType(data.loggable_type) }}#{{ data.loggable_id }}
                    </span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="detailOpen"
            header="Activity detail"
            modal
            :style="{ width: 'min(700px, 95vw)' }"
        >
            <div v-if="detail" class="flex flex-col gap-3 text-sm">
                <div class="grid grid-cols-2 gap-3">
                    <div><span class="text-muted-color">When:</span>
                        {{ detail.created_at ? new Date(detail.created_at).toLocaleString() : '—' }}
                    </div>
                    <div><span class="text-muted-color">Tenant:</span> {{ detail.tenant?.name ?? '—' }}</div>
                    <div><span class="text-muted-color">User:</span> {{ detail.user?.name ?? 'system' }}</div>
                    <div><span class="text-muted-color">Action:</span>
                        <span class="font-mono">{{ detail.action }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-muted-color">Target:</span>
                        <span v-if="detail.loggable_type" class="font-mono text-xs">
                            {{ detail.loggable_type }}#{{ detail.loggable_id }}
                        </span>
                        <span v-else class="text-muted-color">—</span>
                    </div>
                </div>
                <div>
                    <div class="text-muted-color mb-1">Metadata</div>
                    <pre class="text-xs bg-surface-100 dark:bg-surface-800 p-2 rounded overflow-auto max-h-96">{{ JSON.stringify(detail.metadata ?? {}, null, 2) }}</pre>
                </div>
            </div>
        </Dialog>
    </div>
</template>
