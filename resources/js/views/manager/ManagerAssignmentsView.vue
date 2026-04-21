<script setup lang="ts">
import api from '@/lib/api';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Select from 'primevue/select';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

interface AssignRow {
    id: number;
    case_number?: string;
    stage?: string;
    amount?: number;
    currency?: string;
    created_at?: string;
    assigned_to?: number | null;
}

interface TeamMember {
    id: number;
    name: string;
}

const chargebacks = ref<AssignRow[]>([]);
const preventions = ref<AssignRow[]>([]);
const rdr = ref<AssignRow[]>([]);
const team = ref<TeamMember[]>([]);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/manager/assignments');
        chargebacks.value = data.chargebacks ?? [];
        preventions.value = data.preventions ?? [];
        rdr.value = data.rdr ?? [];
        team.value = data.team ?? [];
    } finally {
        loading.value = false;
    }
}

async function assign(kind: 'chargebacks' | 'preventions' | 'rdr', id: number, userId: number | null) {
    await api.post(`/api/v1/manager/assignments/${kind}/${id}`, { user_id: userId });
    await load();
}

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">Assignments queue</div>
        <p class="text-muted-color mb-4">Unassigned cases across channels — pick an owner.</p>

        <Tabs value="0">
            <TabList>
                <Tab value="0">Chargebacks ({{ chargebacks.length }})</Tab>
                <Tab value="1">Preventions ({{ preventions.length }})</Tab>
                <Tab value="2">RDR ({{ rdr.length }})</Tab>
            </TabList>
            <TabPanels>
                <TabPanel value="0">
                    <DataTable :value="chargebacks" :loading="loading" data-key="id" striped-rows size="small">
                        <template #empty>No unassigned chargebacks.</template>
                        <Column field="case_number" header="Case" />
                        <Column header="Stage" style="width: 10rem">
                            <template #body="{ data }"><Tag :value="data.stage" severity="secondary" /></template>
                        </Column>
                        <Column field="amount" header="Amount" style="width: 10rem" />
                        <Column header="Assign to" style="width: 18rem">
                            <template #body="{ data }">
                                <Select
                                    :options="team"
                                    option-label="name"
                                    option-value="id"
                                    :model-value="data.assigned_to"
                                    placeholder="Select member"
                                    @update:model-value="(v) => assign('chargebacks', data.id, v)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
                <TabPanel value="1">
                    <DataTable :value="preventions" :loading="loading" data-key="id" striped-rows size="small">
                        <template #empty>No unassigned preventions.</template>
                        <Column field="case_number" header="Case" />
                        <Column header="Stage" style="width: 10rem">
                            <template #body="{ data }"><Tag :value="data.stage" severity="secondary" /></template>
                        </Column>
                        <Column header="Assign to" style="width: 18rem">
                            <template #body="{ data }">
                                <Select
                                    :options="team"
                                    option-label="name"
                                    option-value="id"
                                    :model-value="data.assigned_to"
                                    placeholder="Select member"
                                    @update:model-value="(v) => assign('preventions', data.id, v)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
                <TabPanel value="2">
                    <DataTable :value="rdr" :loading="loading" data-key="id" striped-rows size="small">
                        <template #empty>No unassigned RDR cases.</template>
                        <Column field="case_number" header="Case" />
                        <Column header="Stage" style="width: 10rem">
                            <template #body="{ data }"><Tag :value="data.stage" severity="secondary" /></template>
                        </Column>
                        <Column header="Assign to" style="width: 18rem">
                            <template #body="{ data }">
                                <Select
                                    :options="team"
                                    option-label="name"
                                    option-value="id"
                                    :model-value="data.assigned_to"
                                    placeholder="Select member"
                                    @update:model-value="(v) => assign('rdr', data.id, v)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
            </TabPanels>
        </Tabs>
    </div>
</template>
