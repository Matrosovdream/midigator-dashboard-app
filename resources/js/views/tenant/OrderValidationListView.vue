<script setup lang="ts">
import { useSavedFilters } from '@/composables/useSavedFilters';
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);
const filters = ref({ search: '' });
useSavedFilters('validations', filters);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/order-validations', {
            params: { page: page.value + 1, per_page: perPage.value, search: filters.value.search || undefined },
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

function onPage(e: DataTablePageEvent) {
    page.value = e.page;
    perPage.value = e.rows;
    load();
}

function resetAndLoad() {
    page.value = 0;
    load();
}

async function act(id: number, stage: string) {
    await api.post(`/api/v1/order-validations/${id}/stage`, { stage });
    load();
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Order Validations</div>
                <p class="text-muted-color m-0">Fraud / AVS / CVV review queue.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText v-model="filters.search" placeholder="Search order, flag" class="w-full" @keyup.enter="resetAndLoad" />
            </IconField>
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
            @page="onPage"
        >
            <template #empty>No validations in queue.</template>
            <Column field="order_reference" header="Order" />
            <Column field="flag" header="Flag">
                <template #body="{ data }"><Tag :value="data.flag ?? '—'" severity="warn" /></template>
            </Column>
            <Column field="avs_result" header="AVS" style="width: 6rem" />
            <Column field="cvv_result" header="CVV" style="width: 6rem" />
            <Column header="Stage" style="width: 10rem">
                <template #body="{ data }"><Tag :value="data.stage ?? '—'" /></template>
            </Column>
            <Column header="" style="width: 12rem">
                <template #body="{ data }">
                    <Button icon="pi pi-check" text rounded severity="success" v-tooltip="'Approve'" @click="act(data.id, 'approved')" />
                    <Button icon="pi pi-times" text rounded severity="danger" v-tooltip="'Reject'" @click="act(data.id, 'rejected')" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
