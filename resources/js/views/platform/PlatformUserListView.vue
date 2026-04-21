<script setup lang="ts">
import api from '@/lib/api';
import { useAuthStore } from '@/stores/auth';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

interface PlatformUser {
    id: number;
    tenant_id: number | null;
    tenant: { id: number; name: string; slug: string } | null;
    email: string;
    name: string;
    avatar: string | null;
    is_active: boolean;
    is_platform_admin: boolean;
    last_login_at: string | null;
    roles: { id: number; name: string }[];
    created_at: string | null;
}

interface TenantOption {
    id: number;
    name: string;
}

const auth = useAuthStore();
const router = useRouter();

const rows = ref<PlatformUser[]>([]);
const loading = ref(false);
const busyId = ref<number | null>(null);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);

const search = ref('');
const tenantFilter = ref<number | null>(null);
const activeFilter = ref<boolean | null>(null);
const platformAdminFilter = ref<boolean | null>(null);

const tenantOptions = ref<TenantOption[]>([]);

const booleanOptions = [
    { label: 'Any', value: null },
    { label: 'Yes', value: true },
    { label: 'No', value: false },
];

async function loadTenantOptions() {
    const { data } = await api.get('/api/v1/platform/tenants', { params: { per_page: 200 } });
    tenantOptions.value = (data.items ?? []).map((t: any) => ({ id: t.id, name: t.name }));
}

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/users', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: search.value || undefined,
                tenant_id: tenantFilter.value ?? undefined,
                is_active: activeFilter.value === null ? undefined : activeFilter.value ? 1 : 0,
                is_platform_admin: platformAdminFilter.value === null ? undefined : platformAdminFilter.value ? 1 : 0,
            },
        });
        rows.value = data.items ?? [];
        totalRecords.value = data.Model?.total ?? rows.value.length;
    } finally {
        loading.value = false;
    }
}

function onPage(event: DataTablePageEvent) {
    page.value = event.page;
    perPage.value = event.rows;
    load();
}

function resetAndLoad() {
    page.value = 0;
    load();
}

function viewTenant(tenantId: number | null) {
    if (!tenantId) return;
    router.push({ name: 'platform.tenants.show', params: { id: tenantId } });
}

async function impersonate(id: number) {
    busyId.value = id;
    try {
        await auth.startImpersonation(id);
        router.push({ name: 'dashboard' });
    } catch (e: any) {
        alert(e?.response?.data?.message ?? 'Unable to start impersonation');
    } finally {
        busyId.value = null;
    }
}

async function toggleActive(row: PlatformUser) {
    busyId.value = row.id;
    try {
        const { data } = await api.post(`/api/v1/platform/users/${row.id}/toggle-active`);
        Object.assign(row, data.user);
    } catch (e: any) {
        alert(e?.response?.data?.message ?? 'Failed');
    } finally {
        busyId.value = null;
    }
}

async function togglePlatformAdmin(row: PlatformUser) {
    busyId.value = row.id;
    try {
        const { data } = await api.post(`/api/v1/platform/users/${row.id}/toggle-platform-admin`);
        Object.assign(row, data.user);
    } catch (e: any) {
        alert(e?.response?.data?.message ?? 'Failed');
    } finally {
        busyId.value = null;
    }
}

onMounted(() => {
    loadTenantOptions();
    load();
});
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Platform Users</div>
                <p class="text-muted-color m-0">All users across every tenant, including other platform admins.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="search"
                    placeholder="Search name or email"
                    class="w-full"
                    @keyup.enter="resetAndLoad"
                />
            </IconField>
            <Select
                v-model="tenantFilter"
                :options="[{ id: null, name: 'Any tenant' }, ...tenantOptions]"
                option-label="name"
                option-value="id"
                placeholder="Tenant"
                class="w-full md:w-56"
                filter
                @change="resetAndLoad"
            />
            <Select
                v-model="activeFilter"
                :options="booleanOptions"
                option-label="label"
                option-value="value"
                placeholder="Active"
                class="w-full md:w-36"
                @change="resetAndLoad"
            />
            <Select
                v-model="platformAdminFilter"
                :options="booleanOptions"
                option-label="label"
                option-value="value"
                placeholder="Platform admin"
                class="w-full md:w-44"
                @change="resetAndLoad"
            />
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
            <template #empty>No users match.</template>

            <Column header="User">
                <template #body="{ data }">
                    <div class="flex items-center gap-2">
                        <Avatar :label="(data.name ?? '?').charAt(0).toUpperCase()" shape="circle" size="normal" />
                        <div>
                            <div class="font-medium">{{ data.name }}</div>
                            <div class="text-muted-color text-xs">{{ data.email }}</div>
                        </div>
                    </div>
                </template>
            </Column>

            <Column header="Tenant">
                <template #body="{ data }">
                    <Button
                        v-if="data.tenant"
                        :label="data.tenant.name"
                        link
                        size="small"
                        @click="viewTenant(data.tenant_id)"
                    />
                    <Tag v-else value="Platform" severity="info" />
                </template>
            </Column>

            <Column header="Roles">
                <template #body="{ data }">
                    <span v-if="data.roles?.length">
                        <Tag
                            v-for="r in data.roles"
                            :key="r.id"
                            :value="r.name"
                            severity="secondary"
                            class="mr-1"
                        />
                    </span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column header="Active" style="width: 7rem">
                <template #body="{ data }">
                    <Tag :value="data.is_active ? 'Active' : 'Inactive'" :severity="data.is_active ? 'success' : 'danger'" />
                </template>
            </Column>

            <Column header="Platform admin" style="width: 9rem">
                <template #body="{ data }">
                    <Tag v-if="data.is_platform_admin" value="Platform admin" severity="warn" />
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column field="last_login_at" header="Last login" style="width: 12rem">
                <template #body="{ data }">
                    <span v-if="data.last_login_at">{{ new Date(data.last_login_at).toLocaleString() }}</span>
                    <span v-else class="text-muted-color">never</span>
                </template>
            </Column>

            <Column header="" style="width: 14rem">
                <template #body="{ data }">
                    <div class="flex gap-1">
                        <Button
                            v-if="!data.is_platform_admin && data.is_active && data.id !== auth.user?.id"
                            icon="pi pi-eye"
                            size="small"
                            text
                            aria-label="Impersonate"
                            v-tooltip.top="'Impersonate'"
                            :loading="busyId === data.id"
                            @click="impersonate(data.id)"
                        />
                        <Button
                            v-if="data.id !== auth.user?.id"
                            :icon="data.is_active ? 'pi pi-ban' : 'pi pi-check'"
                            :severity="data.is_active ? 'warn' : 'success'"
                            size="small"
                            text
                            v-tooltip.top="data.is_active ? 'Deactivate' : 'Activate'"
                            :loading="busyId === data.id"
                            @click="toggleActive(data)"
                        />
                        <Button
                            v-if="data.id !== auth.user?.id"
                            icon="pi pi-shield"
                            :severity="data.is_platform_admin ? 'warn' : 'secondary'"
                            size="small"
                            text
                            v-tooltip.top="data.is_platform_admin ? 'Revoke platform admin' : 'Grant platform admin'"
                            :loading="busyId === data.id"
                            @click="togglePlatformAdmin(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
