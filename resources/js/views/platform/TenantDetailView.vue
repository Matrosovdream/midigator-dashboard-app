<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanel from 'primevue/tabpanel';
import TabPanels from 'primevue/tabpanels';
import Tabs from 'primevue/tabs';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

interface TenantSummary {
    id: number;
    name: string;
    slug: string;
    domain: string | null;
    midigator_sandbox_mode: boolean;
    midigator_webhook_username: string | null;
    is_active: boolean;
    has_api_secret: boolean;
    has_webhook_password: boolean;
    users_count: number;
    created_at: string | null;
}

interface OverviewStats {
    users: number;
    chargebacks: number;
    preventions: number;
    orders: number;
    rdr_cases: number;
    webhooks_total: number;
    webhooks_failed: number;
}

interface WebhookLog {
    id: number;
    event_type: string | null;
    event_guid: string | null;
    status: string | null;
    error_message: string | null;
    processed_at: string | null;
    created_at: string | null;
}

const route = useRoute();
const router = useRouter();
const confirm = useConfirm();
const auth = useAuthStore();

const tenantId = computed(() => Number(route.params.id));

const tenant = ref<TenantSummary | null>(null);
const stats = ref<OverviewStats | null>(null);
const lastWebhook = ref<WebhookLog | null>(null);
const lastFailedWebhook = ref<WebhookLog | null>(null);
const loading = ref(false);

const activeTab = ref<string>('overview');

const users = ref<any[]>([]);
const usersLoading = ref(false);

const activity = ref<any[]>([]);
const activityLoading = ref(false);

const webhooks = ref<WebhookLog[]>([]);
const webhooksLoading = ref(false);

async function loadOverview() {
    loading.value = true;
    try {
        const { data } = await api.get(`/api/v1/platform/tenants/${tenantId.value}/overview`);
        tenant.value = data.tenant;
        stats.value = data.stats;
        lastWebhook.value = data.last_webhook;
        lastFailedWebhook.value = data.last_failed_webhook;
    } finally {
        loading.value = false;
    }
}

async function loadUsers() {
    usersLoading.value = true;
    try {
        const { data } = await api.get(`/api/v1/platform/tenants/${tenantId.value}/users`, {
            params: { per_page: 50 },
        });
        users.value = data.items ?? [];
    } finally {
        usersLoading.value = false;
    }
}

async function loadActivity() {
    activityLoading.value = true;
    try {
        const { data } = await api.get(`/api/v1/platform/tenants/${tenantId.value}/activity`, {
            params: { per_page: 50 },
        });
        activity.value = data.items ?? [];
    } finally {
        activityLoading.value = false;
    }
}

async function loadWebhooks() {
    webhooksLoading.value = true;
    try {
        const { data } = await api.get(`/api/v1/platform/tenants/${tenantId.value}/webhooks`, {
            params: { per_page: 50 },
        });
        webhooks.value = data.items ?? [];
    } finally {
        webhooksLoading.value = false;
    }
}

async function toggleActive() {
    const { data } = await api.post(`/api/v1/platform/tenants/${tenantId.value}/toggle-active`);
    if (tenant.value) tenant.value.is_active = data.tenant.is_active;
}

function confirmDelete() {
    confirm.require({
        message: `Delete tenant "${tenant.value?.name}"? This cannot be undone from the UI.`,
        header: 'Delete tenant',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
        acceptProps: { severity: 'danger' },
        accept: async () => {
            await api.delete(`/api/v1/platform/tenants/${tenantId.value}`);
            router.push({ name: 'platform.tenants.index' });
        },
    });
}

function goEdit() {
    router.push({ name: 'platform.tenants.edit', params: { id: tenantId.value } });
}

async function impersonate(userId: number) {
    try {
        await auth.startImpersonation(userId);
        router.push({ name: 'dashboard' });
    } catch (e: any) {
        const msg = e?.response?.data?.message ?? 'Unable to start impersonation';
        confirm.require({
            message: msg,
            header: 'Impersonation failed',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'OK',
            rejectClass: '!hidden',
        });
    }
}

function goBack() {
    router.push({ name: 'platform.tenants.index' });
}

function severityFor(status: string | null): 'success' | 'warn' | 'danger' | 'secondary' {
    if (!status) return 'secondary';
    if (status === 'processed') return 'success';
    if (status === 'failed') return 'danger';
    return 'warn';
}

watch(activeTab, (tab) => {
    if (tab === 'users' && users.value.length === 0) loadUsers();
    if (tab === 'activity' && activity.value.length === 0) loadActivity();
    if (tab === 'integration' && webhooks.value.length === 0) loadWebhooks();
});

onMounted(loadOverview);
</script>

<template>
    <ConfirmDialog />

    <div class="card">
        <div v-if="loading" class="text-muted-color">Loading…</div>

        <template v-else-if="tenant">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <Button label="Back" icon="pi pi-arrow-left" text size="small" @click="goBack" />
                    <div class="font-semibold text-2xl">{{ tenant.name }}</div>
                    <div class="text-muted-color text-sm">
                        {{ tenant.slug }}
                        <span v-if="tenant.domain"> · {{ tenant.domain }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Tag
                        :value="tenant.midigator_sandbox_mode ? 'Sandbox' : 'Live'"
                        :severity="tenant.midigator_sandbox_mode ? 'warn' : 'success'"
                    />
                    <Tag
                        :value="tenant.is_active ? 'Active' : 'Suspended'"
                        :severity="tenant.is_active ? 'success' : 'danger'"
                    />
                    <Button label="Edit" icon="pi pi-pencil" outlined @click="goEdit" />
                </div>
            </div>

            <Tabs v-model:value="activeTab">
                <TabList>
                    <Tab value="overview">Overview</Tab>
                    <Tab value="users">Users</Tab>
                    <Tab value="integration">Integration</Tab>
                    <Tab value="activity">Activity</Tab>
                    <Tab value="danger">Danger zone</Tab>
                </TabList>
                <TabPanels>
                    <!-- Overview -->
                    <TabPanel value="overview">
                        <div class="grid grid-cols-12 gap-4">
                            <div v-for="kpi in [
                                { label: 'Users', value: stats?.users },
                                { label: 'Chargebacks', value: stats?.chargebacks },
                                { label: 'Preventions', value: stats?.preventions },
                                { label: 'Orders', value: stats?.orders },
                                { label: 'RDR Cases', value: stats?.rdr_cases },
                                { label: 'Webhooks (total)', value: stats?.webhooks_total },
                                { label: 'Webhooks (failed)', value: stats?.webhooks_failed },
                            ]" :key="kpi.label" class="col-span-6 md:col-span-3">
                                <div class="card !mb-0">
                                    <div class="text-muted-color text-xs mb-1">{{ kpi.label }}</div>
                                    <div class="text-2xl font-semibold">{{ kpi.value ?? '—' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-4 mt-4">
                            <div class="col-span-12 md:col-span-6">
                                <div class="card !mb-0">
                                    <div class="font-semibold mb-2">Last webhook</div>
                                    <template v-if="lastWebhook">
                                        <div class="flex items-center gap-2 mb-1">
                                            <Tag :value="lastWebhook.status ?? '—'" :severity="severityFor(lastWebhook.status)" />
                                            <span class="font-mono text-sm">{{ lastWebhook.event_type ?? '—' }}</span>
                                        </div>
                                        <div class="text-muted-color text-xs">{{ lastWebhook.created_at }}</div>
                                    </template>
                                    <div v-else class="text-muted-color">No webhooks received.</div>
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-6">
                                <div class="card !mb-0">
                                    <div class="font-semibold mb-2">Last failure</div>
                                    <template v-if="lastFailedWebhook">
                                        <div class="flex items-center gap-2 mb-1">
                                            <Tag value="failed" severity="danger" />
                                            <span class="font-mono text-sm">{{ lastFailedWebhook.event_type ?? '—' }}</span>
                                        </div>
                                        <div class="text-red-500 text-sm mb-1">{{ lastFailedWebhook.error_message }}</div>
                                        <div class="text-muted-color text-xs">{{ lastFailedWebhook.created_at }}</div>
                                    </template>
                                    <div v-else class="text-muted-color">No failures recorded.</div>
                                </div>
                            </div>
                        </div>
                    </TabPanel>

                    <!-- Users -->
                    <TabPanel value="users">
                        <DataTable :value="users" :loading="usersLoading" data-key="id" striped-rows>
                            <template #empty>No users in this tenant.</template>
                            <Column field="name" header="Name" />
                            <Column field="email" header="Email" />
                            <Column header="Roles">
                                <template #body="{ data }">
                                    <span v-if="data.roles?.length">
                                        <Tag v-for="r in data.roles" :key="r.id" :value="r.name" severity="secondary" class="mr-1" />
                                    </span>
                                    <span v-else class="text-muted-color">—</span>
                                </template>
                            </Column>
                            <Column header="Active">
                                <template #body="{ data }">
                                    <Tag :value="data.is_active ? 'Active' : 'Inactive'" :severity="data.is_active ? 'success' : 'danger'" />
                                </template>
                            </Column>
                            <Column field="last_login_at" header="Last login">
                                <template #body="{ data }">
                                    <span v-if="data.last_login_at">{{ new Date(data.last_login_at).toLocaleString() }}</span>
                                    <span v-else class="text-muted-color">—</span>
                                </template>
                            </Column>
                            <Column header="" style="width: 10rem">
                                <template #body="{ data }">
                                    <Button
                                        v-if="!data.is_platform_admin && data.is_active"
                                        icon="pi pi-eye"
                                        label="Impersonate"
                                        size="small"
                                        text
                                        @click="impersonate(data.id)"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>

                    <!-- Integration -->
                    <TabPanel value="integration">
                        <div class="grid grid-cols-12 gap-4 mb-4">
                            <div class="col-span-12 md:col-span-6">
                                <div class="card !mb-0">
                                    <div class="font-semibold mb-2">Credentials</div>
                                    <div class="flex flex-col gap-1 text-sm">
                                        <div>
                                            <span class="text-muted-color">API secret: </span>
                                            <Tag :value="tenant.has_api_secret ? 'configured' : 'missing'" :severity="tenant.has_api_secret ? 'success' : 'danger'" />
                                        </div>
                                        <div>
                                            <span class="text-muted-color">Webhook user: </span>
                                            <span class="font-mono">{{ tenant.midigator_webhook_username ?? '—' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted-color">Webhook password: </span>
                                            <Tag :value="tenant.has_webhook_password ? 'configured' : 'missing'" :severity="tenant.has_webhook_password ? 'success' : 'danger'" />
                                        </div>
                                        <div>
                                            <span class="text-muted-color">Env: </span>
                                            <Tag :value="tenant.midigator_sandbox_mode ? 'Sandbox' : 'Live'" :severity="tenant.midigator_sandbox_mode ? 'warn' : 'success'" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="font-semibold mb-2">Recent webhooks</div>
                        <DataTable :value="webhooks" :loading="webhooksLoading" data-key="id" striped-rows>
                            <template #empty>No webhook activity.</template>
                            <Column field="created_at" header="Received">
                                <template #body="{ data }">
                                    <span v-if="data.created_at">{{ new Date(data.created_at).toLocaleString() }}</span>
                                </template>
                            </Column>
                            <Column field="event_type" header="Event" />
                            <Column header="Status">
                                <template #body="{ data }">
                                    <Tag :value="data.status ?? '—'" :severity="severityFor(data.status)" />
                                </template>
                            </Column>
                            <Column field="error_message" header="Error">
                                <template #body="{ data }">
                                    <span v-if="data.error_message" class="text-red-500 text-xs">{{ data.error_message }}</span>
                                    <span v-else class="text-muted-color">—</span>
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>

                    <!-- Activity -->
                    <TabPanel value="activity">
                        <DataTable :value="activity" :loading="activityLoading" data-key="id" striped-rows>
                            <template #empty>No activity recorded.</template>
                            <Column field="created_at" header="When">
                                <template #body="{ data }">
                                    <span v-if="data.created_at">{{ new Date(data.created_at).toLocaleString() }}</span>
                                </template>
                            </Column>
                            <Column header="User">
                                <template #body="{ data }">
                                    <span v-if="data.user">{{ data.user.name }}</span>
                                    <span v-else class="text-muted-color">—</span>
                                </template>
                            </Column>
                            <Column field="action" header="Action" />
                            <Column header="Target">
                                <template #body="{ data }">
                                    <span v-if="data.loggable_type" class="font-mono text-xs">{{ data.loggable_type }}#{{ data.loggable_id }}</span>
                                    <span v-else class="text-muted-color">—</span>
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>

                    <!-- Danger -->
                    <TabPanel value="danger">
                        <div class="flex flex-col gap-3 max-w-xl">
                            <div class="card !mb-0 border border-surface-200 dark:border-surface-700">
                                <div class="font-semibold mb-1">{{ tenant.is_active ? 'Suspend tenant' : 'Activate tenant' }}</div>
                                <p class="text-muted-color text-sm">
                                    {{ tenant.is_active
                                        ? 'Suspending prevents tenant users from logging in and stops accepting webhooks.'
                                        : 'Re-enable login and webhook processing for this tenant.' }}
                                </p>
                                <Button
                                    :label="tenant.is_active ? 'Suspend' : 'Activate'"
                                    :severity="tenant.is_active ? 'warn' : 'success'"
                                    outlined
                                    @click="toggleActive"
                                />
                            </div>
                            <div class="card !mb-0 border border-red-300">
                                <div class="font-semibold text-red-500 mb-1">Delete tenant</div>
                                <p class="text-muted-color text-sm">
                                    Permanently removes the tenant record. Associated data may be retained via foreign-key nulling.
                                </p>
                                <Button label="Delete tenant" icon="pi pi-trash" severity="danger" @click="confirmDelete" />
                            </div>
                        </div>
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </template>
    </div>
</template>
