<script setup lang="ts">
import { useCaseComments } from '@/composables/useCaseComments';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import { onMounted, toRef } from 'vue';

const props = defineProps<{
    targetType: 'order' | 'chargeback' | 'prevention' | 'rdr' | 'order_validation';
    targetId: number | string;
    initial?: { id: number; body: string; user?: any; created_at: string }[];
}>();

const targetIdRef = toRef(props, 'targetId');
const { comments, draft, submitting, fetch, submit, seed } = useCaseComments(props.targetType, () => targetIdRef.value);

onMounted(async () => {
    if (props.initial?.length) {
        seed(props.initial);
    } else {
        await fetch();
    }
});
</script>

<template>
    <div>
        <div v-if="!comments.length" class="text-muted-color text-sm">No comments.</div>
        <div v-for="c in comments" :key="c.id" class="mb-3 pb-3 border-b border-surface-200 dark:border-surface-700 last:border-0">
            <div class="text-xs text-muted-color">{{ c.user?.name ?? 'User' }} · {{ new Date(c.created_at).toLocaleString() }}</div>
            <div>{{ c.body }}</div>
        </div>
        <div class="flex flex-col gap-2 mt-2">
            <Textarea v-model="draft" rows="3" placeholder="Add a comment…" />
            <Button label="Post comment" icon="pi pi-comment" class="self-end" :loading="submitting" @click="submit" />
        </div>
    </div>
</template>
