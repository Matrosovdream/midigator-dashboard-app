<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const query = ref('');
const loading = ref(false);
const results = ref<{ type: string; id: number; label: string; meta?: string }[]>([]);

async function run() {
    if (!query.value.trim()) return;
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/search', { params: { q: query.value } });
        results.value = data.items ?? [];
    } catch {
        results.value = [];
    } finally {
        loading.value = false;
    }
}

function open(r: { type: string; id: number }) {
    const map: Record<string, string> = {
        order: 'tenant.orders.show',
        chargeback: 'tenant.chargebacks.show',
        prevention: 'tenant.preventions.show',
        rdr: 'tenant.rdr.show',
    };
    const name = map[r.type];
    if (name) router.push({ name, params: { id: r.id } });
}
</script>

<template>
    <div class="card">
        <div class="font-semibold text-2xl mb-2">Search</div>
        <p class="text-muted-color mb-4">Search across orders, chargebacks, preventions, and RDR cases.</p>

        <div class="flex gap-3 mb-4">
            <IconField class="flex-1">
                <InputIcon class="pi pi-search" />
                <InputText v-model="query" placeholder="Type to search…" class="w-full" @keyup.enter="run" />
            </IconField>
            <Button icon="pi pi-search" label="Search" @click="run" />
        </div>

        <div v-if="loading" class="text-muted-color">Searching…</div>
        <div v-else-if="!results.length" class="text-muted-color">No results yet. Enter a term and hit search.</div>
        <div v-else class="flex flex-col divide-y divide-surface-200 dark:divide-surface-700">
            <div v-for="r in results" :key="`${r.type}:${r.id}`" class="py-3 flex justify-between items-center hover:bg-surface-100 dark:hover:bg-surface-800 cursor-pointer" @click="open(r)">
                <div>
                    <div class="font-medium">{{ r.label }}</div>
                    <div class="text-xs text-muted-color">{{ r.meta }}</div>
                </div>
                <Tag :value="r.type" />
            </div>
        </div>
    </div>
</template>
