<script setup lang="ts">
import api from '@/lib/api';
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

interface Tenant {
    id: number;
    name: string;
    slug: string;
    domain: string | null;
    midigator_sandbox_mode: boolean;
    is_active: boolean;
    users_count: number;
    created_at: string | null;
}

const router = useRouter();
const rows = ref<Tenant[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);

const search = ref('');
const activeFilter = ref<boolean | null>(null);
const sandboxFilter = ref<boolean | null>(null);

const booleanOptions = [
    { label: 'Any', value: null },
    { label: 'Yes', value: true },
    { label: 'No', value: false },
];

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/tenants', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: search.value || undefined,
                is_active: activeFilter.value === null ? undefined : activeFilter.value ? 1 : 0,
                sandbox_mode: sandboxFilter.value === null ? undefined : sandboxFilter.value ? 1 : 0,
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

function goCreate() {
    router.push({ name: 'platform.tenants.create' });
}

function goEdit(id: number) {
    router.push({ name: 'platform.tenants.edit', params: { id } });
}

function goShow(id: number) {
    router.push({ name: 'platform.tenants.show', params: { id } });
}

function onRowClick(event: { data: Tenant }) {
    goShow(event.data.id);
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Tenants</div>
                <p class="text-muted-color m-0">Manage all tenants on the platform.</p>
            </div>
            <Button label="New Tenant" icon="pi pi-plus" @click="goCreate" />
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="search"
                    placeholder="Search name, slug, or domain"
                    class="w-full"
                    @keyup.enter="resetAndLoad"
                />
            </IconField>
            <Select
                v-model="activeFilter"
                :options="booleanOptions"
                option-label="label"
                option-value="value"
                placeholder="Active"
                class="w-full md:w-40"
                @change="resetAndLoad"
            />
            <Select
                v-model="sandboxFilter"
                :options="booleanOptions"
                option-label="label"
                option-value="value"
                placeholder="Sandbox"
                class="w-full md:w-40"
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
            row-hover
            @page="onPage"
            @row-click="onRowClick"
        >
            <template #empty>No tenants found.</template>

            <Column field="name" header="Name">
                <template #body="{ data }">
                    <div class="font-medium">{{ data.name }}</div>
                    <div class="text-muted-color text-xs">{{ data.slug }}</div>
                </template>
            </Column>

            <Column field="domain" header="Domain">
                <template #body="{ data }">
                    <span v-if="data.domain">{{ data.domain }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column field="users_count" header="Users" style="width: 6rem">
                <template #body="{ data }">{{ data.users_count }}</template>
            </Column>

            <Column header="Env" style="width: 8rem">
                <template #body="{ data }">
                    <Tag
                        :value="data.midigator_sandbox_mode ? 'Sandbox' : 'Live'"
                        :severity="data.midigator_sandbox_mode ? 'warn' : 'success'"
                    />
                </template>
            </Column>

            <Column header="Active" style="width: 7rem">
                <template #body="{ data }">
                    <Tag
                        :value="data.is_active ? 'Active' : 'Suspended'"
                        :severity="data.is_active ? 'success' : 'danger'"
                    />
                </template>
            </Column>

            <Column field="created_at" header="Created" style="width: 10rem">
                <template #body="{ data }">
                    <span v-if="data.created_at">{{ new Date(data.created_at).toLocaleDateString() }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>

            <Column header="" style="width: 8rem">
                <template #body="{ data }">
                    <Button
                        icon="pi pi-eye"
                        text
                        rounded
                        aria-label="View tenant"
                        @click.stop="goShow(data.id)"
                    />
                    <Button
                        icon="pi pi-pencil"
                        text
                        rounded
                        aria-label="Edit tenant"
                        @click.stop="goEdit(data.id)"
                    />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
