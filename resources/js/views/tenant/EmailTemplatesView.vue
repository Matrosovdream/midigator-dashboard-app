<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);
const dialog = ref(false);
const editing = ref<any>({ id: null, name: '', subject: '', body: '' });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/email-templates');
        rows.value = data.items ?? [];
    } catch {
        rows.value = [];
    } finally {
        loading.value = false;
    }
}

function open(row: any | null = null) {
    editing.value = row ? { ...row } : { id: null, name: '', subject: '', body: '' };
    dialog.value = true;
}

async function save() {
    if (editing.value.id) {
        await api.patch(`/api/v1/email-templates/${editing.value.id}`, editing.value);
    } else {
        await api.post('/api/v1/email-templates', editing.value);
    }
    dialog.value = false;
    load();
}

async function remove(id: number) {
    if (!confirm('Delete this template?')) return;
    await api.delete(`/api/v1/email-templates/${id}`);
    load();
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <div>
                <div class="font-semibold text-2xl">Email Templates</div>
                <p class="text-muted-color m-0">Templates used for outbound communications.</p>
            </div>
            <Button label="New Template" icon="pi pi-plus" @click="open(null)" />
        </div>

        <DataTable :value="rows" :loading="loading" data-key="id" striped-rows>
            <template #empty>No templates yet.</template>
            <Column field="name" header="Name" />
            <Column field="subject" header="Subject" />
            <Column field="updated_at" header="Updated" style="width: 12rem">
                <template #body="{ data }">{{ data.updated_at ? new Date(data.updated_at).toLocaleDateString() : '—' }}</template>
            </Column>
            <Column header="" style="width: 8rem">
                <template #body="{ data }">
                    <Button icon="pi pi-pencil" text rounded @click="open(data)" />
                    <Button icon="pi pi-trash" text rounded severity="danger" @click="remove(data.id)" />
                </template>
            </Column>
        </DataTable>

        <Dialog v-model:visible="dialog" :header="editing.id ? 'Edit template' : 'New template'" modal :style="{ width: '40rem' }">
            <div class="flex flex-col gap-3">
                <div><label class="text-xs text-muted-color">Name</label><InputText v-model="editing.name" class="w-full" /></div>
                <div><label class="text-xs text-muted-color">Subject</label><InputText v-model="editing.subject" class="w-full" /></div>
                <div><label class="text-xs text-muted-color">Body</label><Textarea v-model="editing.body" rows="10" class="w-full font-mono" /></div>
            </div>
            <template #footer>
                <Button label="Cancel" text @click="dialog = false" />
                <Button label="Save" icon="pi pi-save" @click="save" />
            </template>
        </Dialog>
    </div>
</template>
