<script setup lang="ts">
import api from '@/lib/api';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { computed, onMounted, ref } from 'vue';

interface Right {
    id: number;
    name: string;
    slug: string;
    group: string;
    description: string | null;
    roles_count: number | null;
}

interface Group {
    name: string;
    rights: Right[];
}

const groups = ref<Group[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/rights');
        groups.value = data.groups ?? [];
        total.value = data.total ?? 0;
    } finally {
        loading.value = false;
    }
}

const filteredGroups = computed<Group[]>(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return groups.value;
    return groups.value
        .map((g) => ({
            name: g.name,
            rights: g.rights.filter(
                (r) =>
                    r.slug.toLowerCase().includes(q) ||
                    r.name.toLowerCase().includes(q) ||
                    (r.description ?? '').toLowerCase().includes(q),
            ),
        }))
        .filter((g) => g.rights.length > 0);
});

onMounted(load);
</script>

<template>
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Rights Catalog</div>
                <p class="text-muted-color m-0">
                    All permission rights defined on the platform ({{ total }} total).
                </p>
            </div>
            <IconField class="w-full md:w-80">
                <InputIcon class="pi pi-search" />
                <InputText v-model="search" placeholder="Filter rights" class="w-full" />
            </IconField>
        </div>

        <div v-if="loading" class="text-muted-color">Loading…</div>

        <div v-else class="grid grid-cols-12 gap-4">
            <div
                v-for="group in filteredGroups"
                :key="group.name"
                class="col-span-12 md:col-span-6 xl:col-span-4"
            >
                <div class="card !mb-0 h-full">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold capitalize">{{ group.name.replace('_', ' ') }}</div>
                        <Tag :value="`${group.rights.length} rights`" severity="secondary" />
                    </div>
                    <div class="flex flex-col gap-3">
                        <div v-for="right in group.rights" :key="right.id" class="border-b border-surface-200 dark:border-surface-700 pb-2 last:border-b-0 last:pb-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs text-primary-500">{{ right.slug }}</span>
                                <Tag
                                    v-if="right.roles_count !== null"
                                    :value="`${right.roles_count} role${right.roles_count === 1 ? '' : 's'}`"
                                    :severity="right.roles_count === 0 ? 'secondary' : 'info'"
                                    class="text-xs"
                                />
                            </div>
                            <div class="font-medium text-sm">{{ right.name }}</div>
                            <div v-if="right.description" class="text-muted-color text-xs">{{ right.description }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredGroups.length === 0" class="col-span-12 text-muted-color">
                No rights match your filter.
            </div>
        </div>
    </div>
</template>
