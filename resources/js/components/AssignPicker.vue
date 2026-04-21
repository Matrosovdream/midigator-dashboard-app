<script setup lang="ts">
import api from '@/lib/api';
import Select from 'primevue/select';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    targetType: 'chargeback' | 'prevention' | 'rdr' | 'order_validation';
    targetId: number | string;
    modelValue: { id: number; name: string } | null;
}>();
const emit = defineEmits<{ 'update:modelValue': [v: { id: number; name: string } | null] }>();

const users = ref<{ id: number; name: string }[]>([]);
const loading = ref(false);

async function loadUsers() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/users', { params: { per_page: 200 } });
        users.value = (data.items ?? []).map((u: any) => ({ id: u.id, name: u.name }));
    } catch {
        users.value = [];
    } finally {
        loading.value = false;
    }
}

async function assign(userId: number | null) {
    const url = endpoint(props.targetType);
    await api.post(url, { user_id: userId });
    const picked = users.value.find((u) => u.id === userId) ?? null;
    emit('update:modelValue', picked);
}

function endpoint(type: typeof props.targetType): string {
    const map: Record<string, string> = {
        chargeback: 'chargebacks',
        prevention: 'preventions',
        rdr: 'rdr-cases',
        order_validation: 'order-validations',
    };
    return `/api/v1/${map[type]}/${props.targetId}/assign`;
}

onMounted(loadUsers);
</script>

<template>
    <Select
        :model-value="modelValue?.id ?? null"
        :options="users"
        option-label="name"
        option-value="id"
        placeholder="Assign to…"
        :loading="loading"
        show-clear
        class="w-56"
        @update:model-value="assign"
    />
</template>
