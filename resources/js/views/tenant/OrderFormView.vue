<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const submitting = ref(false);
const error = ref<string | null>(null);

const form = ref({
    order_id: '',
    mid: '',
    order_date: new Date() as Date | null,
    order_amount: 0,
    currency: 'USD',
    email: '',
    phone: '',
    card_brand: '',
    card_first_6: '',
    card_last_4: '',
    billing_first_name: '',
    billing_last_name: '',
    processor_auth_code: '',
    processor_transaction_id: '',
    refunded: false,
    refunded_amount: null as number | null,
});

const currencyOptions = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'].map((v) => ({ label: v, value: v }));
const brandOptions = ['', 'visa', 'mastercard', 'amex', 'discover'].map((v) => ({ label: v || '—', value: v }));

async function save() {
    error.value = null;
    submitting.value = true;
    try {
        const payload = {
            ...form.value,
            order_date: form.value.order_date?.toISOString(),
            refunded_amount: form.value.refunded ? form.value.refunded_amount : null,
        };
        const { data } = await api.post('/api/v1/orders', payload);
        const id = data.record?.id;
        if (data.record?.submission_status === 'failed') {
            error.value = `Saved, but Midigator rejected it: ${data.record?.submission_error ?? 'Unknown error'}`;
        }
        if (id) {
            router.push({ name: 'tenant.orders.show', params: { id } });
        }
    } catch (e: any) {
        error.value = e?.response?.data?.message ?? 'Failed to save order.';
    } finally {
        submitting.value = false;
    }
}

function cancel() {
    router.push({ name: 'tenant.orders.index' });
}
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0 flex justify-between items-center">
                <div>
                    <Button icon="pi pi-arrow-left" text @click="cancel" />
                    <span class="font-semibold text-2xl ml-2">New Order</span>
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-8">
            <Message v-if="error" severity="error" :closable="false" class="mb-4">{{ error }}</Message>

            <Card>
                <template #title>Order</template>
                <template #content>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-muted-color">Order ID <span class="text-red-500">*</span></label>
                            <InputText v-model="form.order_id" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">MID <span class="text-red-500">*</span></label>
                            <InputText v-model="form.mid" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Order date <span class="text-red-500">*</span></label>
                            <DatePicker v-model="form.order_date" show-time class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Amount (cents) <span class="text-red-500">*</span></label>
                            <InputNumber v-model="form.order_amount" :min="0" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Currency <span class="text-red-500">*</span></label>
                            <Select v-model="form.currency" :options="currencyOptions" option-label="label" option-value="value" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Customer email</label>
                            <InputText v-model="form.email" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Phone</label>
                            <InputText v-model="form.phone" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Billing first name</label>
                            <InputText v-model="form.billing_first_name" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Billing last name</label>
                            <InputText v-model="form.billing_last_name" class="w-full" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Card & processor</template>
                <template #content>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-muted-color">Card brand</label>
                            <Select v-model="form.card_brand" :options="brandOptions" option-label="label" option-value="value" class="w-full" />
                        </div>
                        <div></div>
                        <div>
                            <label class="text-xs text-muted-color">Card first 6</label>
                            <InputText v-model="form.card_first_6" class="w-full" maxlength="6" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Card last 4</label>
                            <InputText v-model="form.card_last_4" class="w-full" maxlength="4" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Processor auth code</label>
                            <InputText v-model="form.processor_auth_code" class="w-full" />
                        </div>
                        <div>
                            <label class="text-xs text-muted-color">Processor transaction ID</label>
                            <InputText v-model="form.processor_transaction_id" class="w-full" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="mt-4">
                <template #title>Refund</template>
                <template #content>
                    <div class="grid grid-cols-2 gap-4 items-end">
                        <div class="flex items-center gap-3">
                            <ToggleSwitch v-model="form.refunded" /> <span class="text-sm">Refunded</span>
                        </div>
                        <div v-if="form.refunded">
                            <label class="text-xs text-muted-color">Refund amount (cents)</label>
                            <InputNumber v-model="form.refunded_amount" :min="0" class="w-full" />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <div class="col-span-12 xl:col-span-4">
            <Card>
                <template #title>Save</template>
                <template #content>
                    <p class="text-sm text-muted-color mb-3">
                        The order is saved to the database and forwarded to Midigator. If Midigator rejects it, the record is kept with status <strong>failed</strong> so you can retry from the detail page.
                    </p>
                    <div class="flex flex-col gap-2">
                        <Button label="Save & Submit" icon="pi pi-save" :loading="submitting" @click="save" />
                        <Button label="Cancel" text @click="cancel" />
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
