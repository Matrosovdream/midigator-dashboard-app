<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';
import ToggleSwitch from 'primevue/toggleswitch';
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

interface TenantForm {
    name: string;
    slug: string;
    domain: string;
    midigator_api_secret: string;
    midigator_sandbox_mode: boolean;
    midigator_webhook_username: string;
    midigator_webhook_password: string;
    is_active: boolean;
}

const route = useRoute();
const router = useRouter();

const tenantId = computed(() => {
    const id = route.params.id;
    return id ? Number(id) : null;
});
const isEdit = computed(() => tenantId.value !== null);

const form = reactive<TenantForm>({
    name: '',
    slug: '',
    domain: '',
    midigator_api_secret: '',
    midigator_sandbox_mode: true,
    midigator_webhook_username: '',
    midigator_webhook_password: '',
    is_active: true,
});

const hasApiSecret = ref(false);
const hasWebhookPassword = ref(false);

const loading = ref(false);
const saving = ref(false);
const testing = ref(false);
const errors = ref<Record<string, string[]>>({});
const testResult = ref<{ ok: boolean; message: string } | null>(null);

async function loadTenant() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/api/v1/platform/tenants/${tenantId.value}`);
        const t = data.tenant;
        form.name = t.name ?? '';
        form.slug = t.slug ?? '';
        form.domain = t.domain ?? '';
        form.midigator_sandbox_mode = !!t.midigator_sandbox_mode;
        form.midigator_webhook_username = t.midigator_webhook_username ?? '';
        form.is_active = !!t.is_active;
        hasApiSecret.value = !!t.has_api_secret;
        hasWebhookPassword.value = !!t.has_webhook_password;
    } finally {
        loading.value = false;
    }
}

function buildPayload(): Partial<TenantForm> {
    const payload: any = {
        name: form.name,
        domain: form.domain || null,
        midigator_sandbox_mode: form.midigator_sandbox_mode,
        midigator_webhook_username: form.midigator_webhook_username || null,
        is_active: form.is_active,
    };
    if (form.slug) payload.slug = form.slug;
    if (form.midigator_api_secret) payload.midigator_api_secret = form.midigator_api_secret;
    if (form.midigator_webhook_password) payload.midigator_webhook_password = form.midigator_webhook_password;
    return payload;
}

async function save() {
    saving.value = true;
    errors.value = {};
    try {
        if (isEdit.value) {
            await api.patch(`/api/v1/platform/tenants/${tenantId.value}`, buildPayload());
        } else {
            await api.post('/api/v1/platform/tenants', buildPayload());
        }
        router.push({ name: 'platform.tenants.index' });
    } catch (e: any) {
        if (e?.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        } else {
            errors.value = { _: [e?.response?.data?.message ?? 'Save failed'] };
        }
    } finally {
        saving.value = false;
    }
}

async function testConnection() {
    testing.value = true;
    testResult.value = null;
    try {
        const { data } = await api.post('/api/v1/platform/tenants/test-connection', {
            id: tenantId.value ?? undefined,
            midigator_api_secret: form.midigator_api_secret || undefined,
            midigator_sandbox_mode: form.midigator_sandbox_mode,
        });
        testResult.value = { ok: !!data.ok, message: data.message };
    } catch (e: any) {
        testResult.value = { ok: false, message: e?.response?.data?.message ?? 'Test failed' };
    } finally {
        testing.value = false;
    }
}

function cancel() {
    router.push({ name: 'platform.tenants.index' });
}

onMounted(loadTenant);
</script>

<template>
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="font-semibold text-2xl">{{ isEdit ? 'Edit Tenant' : 'New Tenant' }}</div>
                <p class="text-muted-color m-0">Configure tenant identity and Midigator integration.</p>
            </div>
            <div class="flex gap-2">
                <Button label="Cancel" severity="secondary" outlined @click="cancel" />
                <Button label="Save" icon="pi pi-check" :loading="saving" @click="save" />
            </div>
        </div>

        <Message v-if="errors._" severity="error" :closable="false" class="mb-3">{{ errors._[0] }}</Message>

        <div v-if="loading" class="text-muted-color">Loading…</div>

        <div v-else class="grid grid-cols-12 gap-4">
            <!-- Identity -->
            <div class="col-span-12">
                <div class="font-semibold text-lg mb-2">Identity</div>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block mb-1 font-medium">Name *</label>
                <InputText v-model="form.name" class="w-full" :invalid="!!errors.name" />
                <small v-if="errors.name" class="text-red-500">{{ errors.name[0] }}</small>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block mb-1 font-medium">Slug</label>
                <InputText
                    v-model="form.slug"
                    class="w-full"
                    :invalid="!!errors.slug"
                    :placeholder="isEdit ? '' : 'auto-generated if blank'"
                />
                <small v-if="errors.slug" class="text-red-500">{{ errors.slug[0] }}</small>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block mb-1 font-medium">Domain</label>
                <InputText v-model="form.domain" class="w-full" placeholder="example.com" :invalid="!!errors.domain" />
                <small v-if="errors.domain" class="text-red-500">{{ errors.domain[0] }}</small>
            </div>

            <div class="col-span-12 md:col-span-6 flex items-center gap-3 md:mt-6">
                <ToggleSwitch v-model="form.is_active" input-id="is_active" />
                <label for="is_active" class="font-medium">Active</label>
            </div>

            <!-- Midigator integration -->
            <div class="col-span-12 mt-4">
                <div class="font-semibold text-lg mb-2">Midigator integration</div>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block mb-1 font-medium">API Secret</label>
                <Password
                    v-model="form.midigator_api_secret"
                    class="w-full"
                    toggle-mask
                    :feedback="false"
                    :input-class="'w-full'"
                    :placeholder="hasApiSecret ? 'Leave blank to keep existing' : ''"
                />
                <small v-if="errors.midigator_api_secret" class="text-red-500">{{ errors.midigator_api_secret[0] }}</small>
            </div>

            <div class="col-span-12 md:col-span-6 flex items-center gap-3 md:mt-6">
                <ToggleSwitch v-model="form.midigator_sandbox_mode" input-id="sandbox" />
                <label for="sandbox" class="font-medium">Sandbox mode</label>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block mb-1 font-medium">Webhook username</label>
                <InputText
                    v-model="form.midigator_webhook_username"
                    class="w-full"
                    :invalid="!!errors.midigator_webhook_username"
                />
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block mb-1 font-medium">Webhook password</label>
                <Password
                    v-model="form.midigator_webhook_password"
                    class="w-full"
                    toggle-mask
                    :feedback="false"
                    :input-class="'w-full'"
                    :placeholder="hasWebhookPassword ? 'Leave blank to keep existing' : ''"
                />
            </div>

            <div class="col-span-12 flex items-center gap-3 mt-2">
                <Button
                    label="Test connection"
                    icon="pi pi-bolt"
                    severity="secondary"
                    outlined
                    :loading="testing"
                    @click="testConnection"
                />
                <Message
                    v-if="testResult"
                    :severity="testResult.ok ? 'success' : 'error'"
                    :closable="false"
                    class="!m-0"
                >
                    {{ testResult.message }}
                </Message>
            </div>
        </div>
    </div>
</template>
