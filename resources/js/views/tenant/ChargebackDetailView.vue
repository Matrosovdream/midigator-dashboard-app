<script setup lang="ts">
import AssignPicker from '@/components/AssignPicker.vue';
import CaseComments from '@/components/CaseComments.vue';
import EvidenceUploader from '@/components/EvidenceUploader.vue';
import api from '@/lib/api';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const data = ref<any>(null);

async function load() {
    loading.value = true;
    try {
        const res = await api.get(`/api/v1/chargebacks/${route.params.id}`);
        data.value = res.data.item ?? res.data;
    } catch {
        data.value = null;
    } finally {
        loading.value = false;
    }
}

async function changeStage(stage: string) {
    await api.post(`/api/v1/chargebacks/${route.params.id}/stage`, { stage });
    load();
}

onMounted(load);
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                <div>
                    <Button icon="pi pi-arrow-left" text @click="router.push({ name: 'tenant.chargebacks.index' })" />
                    <span class="font-semibold text-2xl ml-2">Chargeback {{ data?.reference ?? `#${route.params.id}` }}</span>
                </div>
                <div class="flex flex-wrap gap-2 items-center">
                    <AssignPicker
                        v-if="data"
                        target-type="chargeback"
                        :target-id="route.params.id as string"
                        :model-value="data.assignee"
                        @update:model-value="(v) => (data.assignee = v)"
                    />
                    <Button label="Respond" icon="pi pi-send" severity="info" @click="changeStage('responded')" />
                    <Button label="Mark Won" icon="pi pi-check" severity="success" @click="changeStage('won')" />
                    <Button label="Mark Lost" icon="pi pi-times" severity="danger" @click="changeStage('lost')" />
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-8">
            <Card>
                <template #title>Dispute details</template>
                <template #content>
                    <div v-if="loading" class="text-muted-color">Loading…</div>
                    <div v-else-if="!data" class="text-muted-color">No data.</div>
                    <div v-else class="grid grid-cols-2 gap-3">
                        <div><span class="text-muted-color text-xs">Reason code</span><div>{{ data.reason_code ?? '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Stage</span><div><Tag :value="data.stage ?? '—'" /></div></div>
                        <div><span class="text-muted-color text-xs">Amount</span><div>{{ data.amount }} {{ data.currency }}</div></div>
                        <div><span class="text-muted-color text-xs">Deadline</span><div>{{ data.deadline_at ? new Date(data.deadline_at).toLocaleString() : '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Assignee</span><div>{{ data.assignee?.name ?? '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Order</span><div>{{ data.order?.order_number ?? '—' }}</div></div>
                    </div>
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Evidence</template>
                <template #content>
                    <EvidenceUploader
                        target-type="chargeback"
                        :target-id="route.params.id as string"
                        :initial="data?.evidence"
                    />
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Comments</template>
                <template #content>
                    <CaseComments target-type="chargeback" :target-id="route.params.id as string" :initial="data?.comments" />
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
                                <div class="text-xs text-muted-color">{{ slotProps.item.user?.name }} · {{ new Date(slotProps.item.created_at).toLocaleString() }}</div>
                            </div>
                        </template>
                    </Timeline>
                    <div v-if="!data?.transitions?.length" class="text-muted-color text-sm">No stage changes yet.</div>
                </template>
            </Card>
        </div>
    </div>
</template>
