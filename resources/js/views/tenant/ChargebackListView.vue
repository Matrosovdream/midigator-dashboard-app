<script setup lang="ts">
import { useSavedFilters } from '@/composables/useSavedFilters';
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

interface Chargeback {
    id: number;
    reference: string | null;
    reason_code: string | null;
    stage: string | null;
    status: string | null;
    amount: number | null;
    currency: string | null;
    deadline_at: string | null;
    assignee: { id: number; name: string } | null;
    created_at: string | null;
}

const router = useRouter();
const rows = ref<Chargeback[]>([]);
const selected = ref<Chargeback[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);

const filters = ref({ search: '', stage: null as string | null });
const { clear: clearFilters } = useSavedFilters('chargebacks', filters);

const stageOptions = [
    { label: 'Any', value: null },
    { label: 'New', value: 'new' },
    { label: 'In progress', value: 'in_progress' },
    { label: 'Responded', value: 'responded' },
    { label: 'Won', value: 'won' },
    { label: 'Lost', value: 'lost' },
];

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/chargebacks', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: filters.value.search || undefined,
                stage: filters.value.stage ?? undefined,
            },
        });
        rows.value = data.items ?? [];
        totalRecords.value = data.Model?.total ?? rows.value.length;
    } catch {
        rows.value = [];
        totalRecords.value = 0;
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

function resetFilters() {
    filters.value.search = '';
    filters.value.stage = null;
    clearFilters();
    resetAndLoad();
}

function goShow(id: number) {
    router.push({ name: 'tenant.chargebacks.show', params: { id } });
}

async function bulkHide() {
    if (!selected.value.length) return;
    if (!confirm(`Hide ${selected.value.length} chargeback(s)?`)) return;
    await api.post('/api/v1/chargebacks/hide/bulk', { ids: selected.value.map((r) => r.id) });
    selected.value = [];
    load();
}

function bulkExport() {
    const ids = selected.value.map((r) => r.id).join(',');
    window.location.href = `/api/v1/export/chargeback.csv?ids=${ids}`;
}

function stageSeverity(stage: string | null): 'success' | 'warn' | 'danger' | 'secondary' | 'info' {
    if (!stage) return 'secondary';
    if (stage === 'won') return 'success';
    if (stage === 'lost') return 'danger';
    if (stage === 'responded') return 'info';
    return 'warn';
}

function deadlineClass(date: string | null): string {
    if (!date) return 'text-muted-color';
    const days = (new Date(date).getTime() - Date.now()) / 86400000;
    if (days < 0) return 'text-red-600 font-semibold';
    if (days < 2) return 'text-orange-500 font-semibold';
    return '';
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Chargebacks</div>
                <p class="text-muted-color m-0">Dispute queue — respond before the deadline.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText v-model="filters.search" placeholder="Search reference, reason code" class="w-full" @keyup.enter="resetAndLoad" />
            </IconField>
            <Select v-model="filters.stage" :options="stageOptions" option-label="label" option-value="value" placeholder="Stage" class="w-full md:w-48" @change="resetAndLoad" />
            <Button icon="pi pi-search" label="Search" @click="resetAndLoad" />
            <Button icon="pi pi-refresh" severity="secondary" outlined v-tooltip.bottom="'Reset filters'" @click="resetFilters" />
        </div>

        <div v-if="selected.length" class="flex items-center gap-2 mb-3 p-3 bg-primary-50 dark:bg-surface-800 rounded">
            <span class="text-sm font-medium">{{ selected.length }} selected</span>
            <Button icon="pi pi-download" label="Export" size="small" severity="secondary" @click="bulkExport" />
            <Button icon="pi pi-eye-slash" label="Hide" size="small" severity="danger" outlined @click="bulkHide" />
            <Button icon="pi pi-times" text size="small" @click="selected = []" />
        </div>

        <DataTable
            v-model:selection="selected"
            :value="rows"
            :loading="loading"
            lazy
            paginator
            :rows="perPage"
            :rows-per-page-options="[10, 20, 50, 100]"
            :total-records="totalRecords"
            :first="page * perPage"
            data-key="id"
            striped-rows
            row-hover
            @page="onPage"
            @row-click="(e: any) => goShow(e.data.id)"
        >
            <template #empty>No chargebacks found.</template>
            <Column selection-mode="multiple" header-style="width: 3rem" />
            <Column field="reference" header="Reference">
                <template #body="{ data }">
                    <span class="font-mono">{{ data.reference ?? `#${data.id}` }}</span>
                </template>
            </Column>
            <Column field="reason_code" header="Reason" style="width: 8rem" />
            <Column header="Amount" style="width: 10rem">
                <template #body="{ data }">
                    <span v-if="data.amount != null">{{ Number(data.amount).toFixed(2) }} {{ data.currency ?? '' }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="Stage" style="width: 10rem">
                <template #body="{ data }">
                    <Tag :value="data.stage ?? '—'" :severity="stageSeverity(data.stage)" />
                </template>
            </Column>
            <Column header="Assignee" style="width: 10rem">
                <template #body="{ data }">{{ data.assignee?.name ?? '—' }}</template>
            </Column>
            <Column header="Deadline" style="width: 12rem">
                <template #body="{ data }">
                    <span v-if="data.deadline_at" :class="deadlineClass(data.deadline_at)">
                        {{ new Date(data.deadline_at).toLocaleDateString() }}
                    </span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="" style="width: 5rem">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" text rounded @click.stop="goShow(data.id)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
