<script setup lang="ts">
import AssignPicker from '@/components/AssignPicker.vue';
import CaseComments from '@/components/CaseComments.vue';
import api from '@/lib/api';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const data = ref<any>(null);
const loading = ref(false);

async function load() {
    loading.value = true;
    try {
        const res = await api.get(`/api/v1/rdr-cases/${route.params.id}`);
        data.value = res.data.item ?? res.data;
    } catch {
        data.value = null;
    } finally {
        loading.value = false;
    }
}

async function resolve(outcome: string) {
    await api.post(`/api/v1/rdr-cases/${route.params.id}/resolve`, { outcome });
    load();
}

onMounted(load);
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                <div>
                    <Button icon="pi pi-arrow-left" text @click="router.push({ name: 'tenant.rdr.index' })" />
                    <span class="font-semibold text-2xl ml-2">RDR {{ data?.case_id ?? `#${route.params.id}` }}</span>
                </div>
                <div class="flex flex-wrap gap-2 items-center">
                    <AssignPicker
                        v-if="data"
                        target-type="rdr"
                        :target-id="route.params.id as string"
                        :model-value="data.assignee"
                        @update:model-value="(v) => (data.assignee = v)"
                    />
                    <Button label="Accept" icon="pi pi-check" severity="success" @click="resolve('accepted')" />
                    <Button label="Dispute" icon="pi pi-times" severity="danger" @click="resolve('disputed')" />
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-8">
            <Card>
                <template #title>Case details</template>
                <template #content>
                    <div v-if="!data" class="text-muted-color">{{ loading ? 'Loading…' : 'No data.' }}</div>
                    <div v-else class="grid grid-cols-2 gap-3">
                        <div><span class="text-muted-color text-xs">Stage</span><div><Tag :value="data.stage ?? '—'" /></div></div>
                        <div><span class="text-muted-color text-xs">Amount</span><div>{{ data.amount }} {{ data.currency }}</div></div>
                        <div><span class="text-muted-color text-xs">Transaction</span><div>{{ data.transaction_id ?? '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Reason</span><div>{{ data.reason ?? '—' }}</div></div>
                    </div>
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Comments</template>
                <template #content>
                    <CaseComments target-type="rdr" :target-id="route.params.id as string" :initial="data?.comments" />
                </template>
            </Card>
        </div>

        <div class="col-span-12 xl:col-span-4">
            <Card>
                <template #title>Timeline</template>
                <template #content>
                    <Timeline :value="data?.transitions ?? []">
                        <template #content="slotProps">
                            <div class="text-sm">
                                <div class="font-medium">{{ slotProps.item.from_stage }} → {{ slotProps.item.to_stage }}</div>
                                <div class="text-xs text-muted-color">{{ new Date(slotProps.item.created_at).toLocaleString() }}</div>
                            </div>
                        </template>
                    </Timeline>
                    <div v-if="!data?.transitions?.length" class="text-muted-color text-sm">No stage changes yet.</div>
                </template>
            </Card>
        </div>
    </div>
</template>
