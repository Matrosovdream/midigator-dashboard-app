<script setup lang="ts">
import CaseComments from '@/components/CaseComments.vue';
import api from '@/lib/api';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import Timeline from 'primevue/timeline';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const submitting = ref(false);
const data = ref<any>(null);

async function load() {
    loading.value = true;
    try {
        const res = await api.get(`/api/v1/orders/${route.params.id}`);
        data.value = res.data.item ?? res.data;
    } catch {
        data.value = null;
    } finally {
        loading.value = false;
    }
}

async function submit() {
    submitting.value = true;
    try {
        const { data: res } = await api.post(`/api/v1/orders/${route.params.id}/submit`);
        if (res.record) data.value = { ...data.value, ...res.record };
        else load();
    } catch {
        load();
    } finally {
        submitting.value = false;
    }
}

function submissionSeverity(status: string | null): 'success' | 'warn' | 'danger' | 'secondary' {
    if (status === 'submitted') return 'success';
    if (status === 'failed') return 'danger';
    if (status === 'pending') return 'warn';
    return 'secondary';
}

const canRetry = computed(() => data.value?.submission_status !== 'submitted');

onMounted(load);
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0 flex justify-between items-center">
                <div>
                    <Button icon="pi pi-arrow-left" text @click="router.push({ name: 'tenant.orders.index' })" />
                    <span class="font-semibold text-2xl ml-2">Order #{{ data?.order_id ?? route.params.id }}</span>
                    <Tag v-if="data?.submission_status" :value="data.submission_status" :severity="submissionSeverity(data.submission_status)" class="ml-3" />
                </div>
                <Button v-if="canRetry" :label="data?.submission_status === 'failed' ? 'Retry submission' : 'Submit to Midigator'" icon="pi pi-send" :loading="submitting" @click="submit" />
            </div>
        </div>

        <div class="col-span-12">
            <Message v-if="data?.submission_status === 'failed'" severity="error" :closable="false">
                Midigator rejected this order: <span class="font-mono">{{ data.submission_error ?? 'Unknown error' }}</span>
            </Message>
        </div>

        <div class="col-span-12 xl:col-span-8">
            <Card>
                <template #title>Details</template>
                <template #content>
                    <div v-if="loading" class="text-muted-color">Loading…</div>
                    <div v-else-if="!data" class="text-muted-color">No data.</div>
                    <div v-else class="grid grid-cols-2 gap-3">
                        <div><span class="text-muted-color text-xs">Amount</span><div>{{ data.order_amount }} {{ data.currency }}</div></div>
                        <div><span class="text-muted-color text-xs">Submission</span><div><Tag :value="data.submission_status ?? '—'" :severity="submissionSeverity(data.submission_status)" /></div></div>
                        <div><span class="text-muted-color text-xs">MID</span><div>{{ data.mid ?? '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Email</span><div>{{ data.email ?? '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Order date</span><div>{{ data.order_date ? new Date(data.order_date).toLocaleString() : '—' }}</div></div>
                        <div><span class="text-muted-color text-xs">Submitted at</span><div>{{ data.submitted_at ? new Date(data.submitted_at).toLocaleString() : '—' }}</div></div>
                    </div>
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Related cases</template>
                <template #content>
                    <DataTable :value="data?.related ?? []" size="small" data-key="id">
                        <template #empty>No linked chargebacks, preventions, or RDR cases.</template>
                        <Column field="type" header="Type" />
                        <Column field="reference" header="Reference" />
                        <Column field="status" header="Status">
                            <template #body="{ data: row }"><Tag :value="row.status ?? '—'" /></template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Comments</template>
                <template #content>
                    <CaseComments target-type="order" :target-id="route.params.id as string" :initial="data?.comments" />
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
