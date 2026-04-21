<script setup lang="ts">
import api from '@/lib/api';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

interface ApprovalRow {
    id: number;
    kind: 'chargeback' | 'prevention' | 'rdr' | 'order_validation';
    target_id: number;
    case_number?: string;
    from_stage?: string;
    to_stage?: string;
    requested_by?: { id: number; name: string } | null;
    created_at?: string;
}

const rows = ref<ApprovalRow[]>([]);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/manager/approvals');
        rows.value = data.items ?? [];
    } catch {
        rows.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">Approvals queue</div>
        <p class="text-muted-color mb-4">Stage-change requests awaiting a manager decision.</p>

        <DataTable :value="rows" :loading="loading" data-key="id" striped-rows size="small">
            <template #empty>Nothing pending — you're caught up.</template>
            <Column header="Kind" style="width: 10rem">
                <template #body="{ data }"><Tag :value="data.kind" severity="info" /></template>
            </Column>
            <Column field="case_number" header="Case" />
            <Column header="From → To" style="width: 14rem">
                <template #body="{ data }">
                    <span class="text-xs">{{ data.from_stage }} → {{ data.to_stage }}</span>
                </template>
            </Column>
            <Column header="Requested by">
                <template #body="{ data }">
                    <span v-if="data.requested_by">{{ data.requested_by.name }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="When">
                <template #body="{ data }">
                    <span v-if="data.created_at" class="text-xs">{{ new Date(data.created_at).toLocaleString() }}</span>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
