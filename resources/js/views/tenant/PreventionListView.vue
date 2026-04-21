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

const router = useRouter();
const rows = ref<any[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);
const filters = ref({ search: '', provider: null as string | null });
useSavedFilters('preventions', filters);

const providerOptions = [
    { label: 'Any', value: null },
    { label: 'Ethoca', value: 'ethoca' },
    { label: 'Verifi', value: 'verifi' },
];

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/preventions', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: filters.value.search || undefined,
                provider: filters.value.provider ?? undefined,
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

function goShow(id: number) {
    router.push({ name: 'tenant.preventions.show', params: { id } });
}

async function act(id: number, action: 'resolve' | 'stage', extra: Record<string, any> = {}) {
    await api.post(`/api/v1/preventions/${id}/${action}`, extra);
    load();
}

function statusSeverity(stage: string | null): 'success' | 'warn' | 'danger' | 'secondary' | 'info' {
    if (!stage) return 'secondary';
    if (stage === 'resolved' || stage === 'refunded') return 'success';
    if (stage === 'declined') return 'danger';
    if (stage === 'new') return 'warn';
    return 'info';
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Preventions</div>
                <p class="text-muted-color m-0">Act on Ethoca / Verifi alerts before they escalate.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText v-model="filters.search" placeholder="Search alert id, transaction" class="w-full" @keyup.enter="resetAndLoad" />
            </IconField>
            <Select v-model="filters.provider" :options="providerOptions" option-label="label" option-value="value" placeholder="Provider" class="w-full md:w-48" @change="resetAndLoad" />
            <Button icon="pi pi-search" label="Search" @click="resetAndLoad" />
        </div>

        <DataTable
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
            <template #empty>No preventions found.</template>
            <Column field="alert_id" header="Alert ID">
                <template #body="{ data }"><span class="font-mono">{{ data.alert_id ?? `#${data.id}` }}</span></template>
            </Column>
            <Column field="provider" header="Provider" style="width: 8rem" />
            <Column header="Amount" style="width: 10rem">
                <template #body="{ data }">
                    <span v-if="data.amount != null">{{ Number(data.amount).toFixed(2) }} {{ data.currency ?? '' }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="Stage" style="width: 10rem">
                <template #body="{ data }"><Tag :value="data.stage ?? '—'" :severity="statusSeverity(data.stage)" /></template>
            </Column>
            <Column header="Received" style="width: 12rem">
                <template #body="{ data }">
                    <span v-if="data.created_at">{{ new Date(data.created_at).toLocaleString() }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="" style="width: 14rem">
                <template #body="{ data }">
                    <Button icon="pi pi-check" text rounded severity="success" v-tooltip="'Refund'" @click.stop="act(data.id, 'resolve', { outcome: 'refunded' })" />
                    <Button icon="pi pi-times" text rounded severity="danger" v-tooltip="'Decline'" @click.stop="act(data.id, 'stage', { stage: 'declined' })" />
                    <Button icon="pi pi-eye" text rounded @click.stop="goShow(data.id)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
