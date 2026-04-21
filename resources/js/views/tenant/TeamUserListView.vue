<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

const rows = ref<any[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);
const search = ref('');

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/users', {
            params: { page: page.value + 1, per_page: perPage.value, search: search.value || undefined },
        });
        rows.value = data.items ?? [];
        totalRecords.value = data.Model?.total ?? rows.value.length;
    } catch {
        rows.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
}

function onPage(e: DataTablePageEvent) {
    page.value = e.page;
    perPage.value = e.rows;
    load();
}

function resetAndLoad() {
    page.value = 0;
    load();
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Team</div>
                <p class="text-muted-color m-0">Users in your tenant.</p>
            </div>
            <Button label="Invite User" icon="pi pi-user-plus" />
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText v-model="search" placeholder="Search name or email" class="w-full" @keyup.enter="resetAndLoad" />
            </IconField>
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
            <template #empty>No users found.</template>
            <Column field="name" header="Name" />
            <Column field="email" header="Email" />
            <Column header="Roles">
                <template #body="{ data }">
                    <Tag v-for="r in data.roles ?? []" :key="r.id" :value="r.name" class="mr-1" severity="secondary" />
                </template>
            </Column>
            <Column header="Status" style="width: 7rem">
                <template #body="{ data }">
                    <Tag :value="data.is_active ? 'Active' : 'Disabled'" :severity="data.is_active ? 'success' : 'danger'" />
                </template>
            </Column>
            <Column header="" style="width: 8rem">
                <template #body>
                    <Button icon="pi pi-pencil" text rounded />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
