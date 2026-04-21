<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { useConfirm } from 'primevue/useconfirm';
import { onMounted, reactive, ref } from 'vue';

interface Template {
    id: number;
    name: string;
    subject: string;
    body: string;
    variables: string[] | null;
    is_active: boolean;
    tenant_override_count: number;
    created_at: string | null;
}

const confirm = useConfirm();

const rows = ref<Template[]>([]);
const loading = ref(false);

const formOpen = ref(false);
const editing = ref<Template | null>(null);
const saving = ref(false);
const errors = ref<Record<string, string[]>>({});
const serverError = ref<string | null>(null);

const form = reactive({
    name: '',
    subject: '',
    body: '',
    is_active: true,
});

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/emails/templates', { params: { per_page: 200 } });
        rows.value = data.items ?? [];
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editing.value = null;
    form.name = '';
    form.subject = '';
    form.body = '';
    form.is_active = true;
    errors.value = {};
    serverError.value = null;
    formOpen.value = true;
}

function openEdit(row: Template) {
    editing.value = row;
    form.name = row.name;
    form.subject = row.subject;
    form.body = row.body;
    form.is_active = row.is_active;
    errors.value = {};
    serverError.value = null;
    formOpen.value = true;
}

async function save() {
    saving.value = true;
    errors.value = {};
    serverError.value = null;
    try {
        if (editing.value) {
            await api.patch(`/api/v1/platform/emails/templates/${editing.value.id}`, form);
        } else {
            await api.post('/api/v1/platform/emails/templates', form);
        }
        formOpen.value = false;
        await load();
    } catch (e: any) {
        if (e?.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
            serverError.value = e.response.data?.message ?? null;
        } else {
            serverError.value = e?.response?.data?.message ?? 'Save failed';
        }
    } finally {
        saving.value = false;
    }
}

function confirmDelete(row: Template) {
    confirm.require({
        message: `Delete global template "${row.name}"? Tenant-specific overrides are not affected.`,
        header: 'Delete template',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
        acceptProps: { severity: 'danger' },
        accept: async () => {
            await api.delete(`/api/v1/platform/emails/templates/${row.id}`);
            await load();
        },
    });
}

onMounted(load);
</script>

<template>
    <ConfirmDialog />

    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Global Email Templates</div>
                <p class="text-muted-color m-0">
                    Default templates shared by all tenants. Individual tenants can override by creating their own with the same name.
                </p>
            </div>
            <Button label="New template" icon="pi pi-plus" @click="openCreate" />
        </div>

        <DataTable :value="rows" :loading="loading" data-key="id" striped-rows>
            <template #empty>No global templates yet.</template>

            <Column field="name" header="Name">
                <template #body="{ data }">
                    <span class="font-mono text-sm">{{ data.name }}</span>
                </template>
            </Column>
            <Column field="subject" header="Subject" />
            <Column header="Active" style="width: 7rem">
                <template #body="{ data }">
                    <Tag :value="data.is_active ? 'Active' : 'Inactive'" :severity="data.is_active ? 'success' : 'secondary'" />
                </template>
            </Column>
            <Column header="Tenant overrides" style="width: 11rem">
                <template #body="{ data }">
                    <Tag
                        :value="`${data.tenant_override_count} override${data.tenant_override_count === 1 ? '' : 's'}`"
                        :severity="data.tenant_override_count > 0 ? 'info' : 'secondary'"
                    />
                </template>
            </Column>
            <Column header="" style="width: 8rem">
                <template #body="{ data }">
                    <Button icon="pi pi-pencil" text rounded @click="openEdit(data)" />
                    <Button icon="pi pi-trash" severity="danger" text rounded @click="confirmDelete(data)" />
                </template>
            </Column>
        </DataTable>

        <Dialog
            v-model:visible="formOpen"
            :header="editing ? `Edit template: ${editing.name}` : 'New global template'"
            modal
            :style="{ width: 'min(800px, 95vw)' }"
        >
            <div class="flex flex-col gap-3">
                <Message v-if="serverError" severity="error" :closable="false">{{ serverError }}</Message>

                <div>
                    <label class="block mb-1 font-medium">Name (key) *</label>
                    <InputText
                        v-model="form.name"
                        class="w-full"
                        :disabled="!!editing"
                        placeholder="e.g. chargeback_assigned"
                        :invalid="!!errors.name"
                    />
                    <small v-if="errors.name" class="text-red-500">{{ errors.name[0] }}</small>
                    <small v-else class="text-muted-color">Stable identifier used by the app code to look up templates.</small>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Subject *</label>
                    <InputText v-model="form.subject" class="w-full" :invalid="!!errors.subject" />
                    <small v-if="errors.subject" class="text-red-500">{{ errors.subject[0] }}</small>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Body (HTML) *</label>
                    <Textarea v-model="form.body" class="w-full" rows="10" :invalid="!!errors.body" />
                    <small v-if="errors.body" class="text-red-500">{{ errors.body[0] }}</small>
                </div>

                <div class="flex items-center gap-3">
                    <ToggleSwitch v-model="form.is_active" input-id="tpl_active" />
                    <label for="tpl_active">Active</label>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" outlined @click="formOpen = false" />
                <Button label="Save" icon="pi pi-check" :loading="saving" @click="save" />
            </template>
        </Dialog>
    </div>
</template>
