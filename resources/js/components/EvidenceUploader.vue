<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import FileUpload, { type FileUploadSelectEvent } from 'primevue/fileupload';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    targetType: 'chargeback' | 'prevention' | 'rdr' | 'order';
    targetId: number | string;
    initial?: any[];
}>();

const items = ref<any[]>([]);
const uploading = ref(false);

function baseUrl(): string {
    return `/api/v1/${props.targetType}/${props.targetId}/evidence`;
}

async function load() {
    try {
        const { data } = await api.get(baseUrl());
        items.value = data.items ?? [];
    } catch {
        items.value = [];
    }
}

async function onSelect(event: FileUploadSelectEvent) {
    const files = event.files as File[];
    if (!files?.length) return;
    uploading.value = true;
    try {
        for (const file of files) {
            const form = new FormData();
            form.append('file', file);
            await api.post(baseUrl(), form, { headers: { 'Content-Type': 'multipart/form-data' } });
        }
        await load();
    } finally {
        uploading.value = false;
    }
}

async function remove(id: number) {
    if (!confirm('Remove this file?')) return;
    await api.delete(`/api/v1/evidence/${id}`);
    await load();
}

watch(
    () => props.targetId,
    () => {
        if (props.initial?.length) items.value = props.initial;
        else load();
    },
);

onMounted(() => {
    if (props.initial?.length) items.value = props.initial;
    else load();
});
</script>

<template>
    <div>
        <DataTable :value="items" size="small">
            <template #empty>No evidence uploaded.</template>
            <Column field="name" header="File">
                <template #body="{ data }">
                    <a v-if="data.url" :href="data.url" target="_blank" class="text-primary-500 hover:underline">{{ data.name }}</a>
                    <span v-else>{{ data.name }}</span>
                </template>
            </Column>
            <Column header="Uploaded by" style="width: 12rem">
                <template #body="{ data }">{{ data.uploader?.name ?? '—' }}</template>
            </Column>
            <Column header="When" style="width: 14rem">
                <template #body="{ data }">{{ data.uploaded_at ? new Date(data.uploaded_at).toLocaleString() : '—' }}</template>
            </Column>
            <Column header="" style="width: 5rem">
                <template #body="{ data }">
                    <Button icon="pi pi-trash" text rounded severity="danger" @click="remove(data.id)" />
                </template>
            </Column>
        </DataTable>
        <div class="mt-3">
            <FileUpload mode="basic" auto custom-upload :multiple="true" :disabled="uploading" choose-label="Upload evidence" choose-icon="pi pi-upload" @select="onSelect" />
        </div>
    </div>
</template>
