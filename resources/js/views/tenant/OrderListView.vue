<script setup lang="ts">
import { useSavedFilters } from '@/composables/useSavedFilters';
import api from '@/lib/api';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable, { type DataTablePageEvent } from 'primevue/datatable';
import DatePicker from 'primevue/datepicker';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

interface Order {
    id: number;
    order_number: string | null;
    amount: number | null;
    currency: string | null;
    status: string | null;
    mid: string | null;
    created_at: string | null;
}

const router = useRouter();
const rows = ref<Order[]>([]);
const loading = ref(false);
const totalRecords = ref(0);
const page = ref(0);
const perPage = ref(20);

const filters = ref({ search: '' });
useSavedFilters('orders', filters);
const dateRange = ref<Date[] | null>(null);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/orders', {
            params: {
                page: page.value + 1,
                per_page: perPage.value,
                search: filters.value.search || undefined,
                date_from: dateRange.value?.[0]?.toISOString().slice(0, 10),
                date_to: dateRange.value?.[1]?.toISOString().slice(0, 10),
            },
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

function onPage(event: DataTablePageEvent) {
    page.value = event.page;
    perPage.value = event.rows;
    load();
}

function resetAndLoad() {
    page.value = 0;
    load();
}

function goShow(id: number) {
    router.push({ name: 'tenant.orders.show', params: { id } });
}

function statusSeverity(status: string | null): 'success' | 'warn' | 'danger' | 'secondary' | 'info' {
    if (!status) return 'secondary';
    const s = status.toLowerCase();
    if (['approved', 'captured', 'completed', 'paid'].includes(s)) return 'success';
    if (['pending', 'processing'].includes(s)) return 'warn';
    if (['declined', 'failed', 'chargeback'].includes(s)) return 'danger';
    return 'info';
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Orders</div>
                <p class="text-muted-color m-0">All orders for your tenant.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText
                    v-model="filters.search"
                    placeholder="Search order #, MID, customer"
                    class="w-full"
                    @keyup.enter="resetAndLoad"
                />
            </IconField>
            <DatePicker v-model="dateRange" selection-mode="range" placeholder="Date range" show-icon class="w-full md:w-72" @date-select="resetAndLoad" />
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
            @row-click="(e: any) => goShow(e.data.id)"
        >
            <template #empty>No orders found.</template>

            <Column field="order_number" header="Order #">
                <template #body="{ data }">
                    <span class="font-mono">{{ data.order_number ?? `#${data.id}` }}</span>
                </template>
            </Column>
            <Column header="Amount" style="width: 10rem">
                <template #body="{ data }">
                    <span v-if="data.amount != null">{{ Number(data.amount).toFixed(2) }} {{ data.currency ?? '' }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column field="mid" header="MID" style="width: 10rem">
                <template #body="{ data }">{{ data.mid ?? '—' }}</template>
            </Column>
            <Column header="Status" style="width: 10rem">
                <template #body="{ data }">
                    <Tag :value="data.status ?? '—'" :severity="statusSeverity(data.status)" />
                </template>
            </Column>
            <Column field="created_at" header="Created" style="width: 12rem">
                <template #body="{ data }">
                    <span v-if="data.created_at">{{ new Date(data.created_at).toLocaleString() }}</span>
                    <span v-else class="text-muted-color">—</span>
                </template>
            </Column>
            <Column header="" style="width: 5rem">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" text rounded @click.stop="goShow(data.id)" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
