<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import FileUpload, { type FileUploadSelectEvent } from 'primevue/fileupload';
import { ref } from 'vue';

const props = defineProps<{
    targetType: 'chargeback' | 'prevention' | 'rdr';
    targetId: number | string;
    items: { id: number; name: string; url?: string; uploaded_at: string }[];
}>();
const emit = defineEmits<{ changed: [] }>();

const uploading = ref(false);

async function onSelect(event: FileUploadSelectEvent) {
    const files = event.files as File[];
    if (!files?.length) return;
    uploading.value = true;
    try {
        for (const file of files) {
            const form = new FormData();
            form.append('file', file);
            form.append('target_type', props.targetType);
            form.append('target_id', String(props.targetId));
            await api.post('/api/v1/evidence', form, { headers: { 'Content-Type': 'multipart/form-data' } });
        }
        emit('changed');
    } finally {
        uploading.value = false;
    }
}

async function remove(id: number) {
    if (!confirm('Remove this file?')) return;
    await api.delete(`/api/v1/evidence/${id}`);
    emit('changed');
}
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
            <Column header="Uploaded" style="width: 14rem">
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
