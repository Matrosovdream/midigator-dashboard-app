<script setup lang="ts">
import api from '@/lib/api';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/activity-log', { params: { page: page.value + 1, per_page: perPage.value } });
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

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">Activity Log</div>
        <p class="text-muted-color mb-4">Audit trail of actions in your tenant.</p>

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
            <template #empty>No activity yet.</template>
            <Column header="When" style="width: 14rem">
                <template #body="{ data }">
                    <span class="text-xs">{{ new Date(data.created_at).toLocaleString() }}</span>
                </template>
            </Column>
            <Column header="User" style="width: 12rem">
                <template #body="{ data }">{{ data.user?.name ?? '—' }}</template>
            </Column>
            <Column header="Action">
                <template #body="{ data }"><Tag :value="data.action" severity="secondary" class="font-mono" /></template>
            </Column>
            <Column header="Target">
                <template #body="{ data }">
                    <span class="text-xs text-muted-color">{{ data.target_type }} #{{ data.target_id }}</span>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
