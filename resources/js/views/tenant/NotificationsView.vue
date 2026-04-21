<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/notifications');
        rows.value = data.items ?? [];
    } catch {
        rows.value = [];
    } finally {
        loading.value = false;
    }
}

async function markRead(id: number) {
    await api.patch(`/api/v1/notifications/${id}/read`);
    load();
}

async function markAllRead() {
    await api.post('/api/v1/notifications/read-all');
    load();
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <div>
                <div class="font-semibold text-2xl">Notifications</div>
                <p class="text-muted-color m-0">Activity that mentions or involves you.</p>
            </div>
            <Button label="Mark all read" icon="pi pi-check-circle" severity="secondary" @click="markAllRead" />
        </div>

        <DataTable :value="rows" :loading="loading" data-key="id" striped-rows>
            <template #empty>No notifications.</template>
            <Column header="When" style="width: 14rem">
                <template #body="{ data }">
                    <span class="text-xs">{{ new Date(data.created_at).toLocaleString() }}</span>
                </template>
            </Column>
            <Column header="Status" style="width: 6rem">
                <template #body="{ data }">
                    <Tag :value="data.read_at ? 'Read' : 'New'" :severity="data.read_at ? 'secondary' : 'info'" />
                </template>
            </Column>
            <Column field="title" header="Title" />
            <Column field="body" header="Message" />
            <Column header="" style="width: 5rem">
                <template #body="{ data }">
                    <Button v-if="!data.read_at" icon="pi pi-check" text rounded v-tooltip="'Mark read'" @click="markRead(data.id)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
