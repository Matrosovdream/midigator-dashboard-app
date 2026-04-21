<script setup lang="ts">
import Button from 'primevue/button';

const types: { key: string; label: string; description: string }[] = [
    { key: 'chargeback', label: 'Chargebacks', description: 'Export all chargebacks (CSV).' },
    { key: 'prevention', label: 'Preventions', description: 'Export prevention alerts (CSV).' },
    { key: 'order', label: 'Orders', description: 'Export orders (CSV).' },
    { key: 'order_validation', label: 'Order Validations', description: 'Export validations (CSV).' },
    { key: 'rdr', label: 'RDR Cases', description: 'Export RDR cases (CSV).' },
];

function download(key: string) {
    window.location.href = `/api/v1/export/${key}.csv`;
}
</script>

<template>
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card !mb-0">
                <div class="font-semibold text-2xl">Exports</div>
                <p class="text-muted-color m-0">Download CSV extracts for your data.</p>
            </div>
        </div>

        <div v-for="t in types" :key="t.key" class="col-span-12 md:col-span-6 xl:col-span-4">
            <div class="card !mb-0 flex flex-col gap-2">
                <span class="font-semibold">{{ t.label }}</span>
                <span class="text-muted-color text-sm">{{ t.description }}</span>
                <Button icon="pi pi-download" label="Download CSV" severity="secondary" class="self-start mt-2" @click="download(t.key)" />
            </div>
        </div>
    </div>
</template>
