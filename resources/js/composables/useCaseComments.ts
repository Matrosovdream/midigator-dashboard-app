import api from '@/lib/api';
import { ref } from 'vue';

export interface CaseComment {
    id: number;
    body: string;
    user?: { id: number; name: string };
    created_at: string;
}

export function useCaseComments(targetType: string, targetId: () => number | string) {
    const comments = ref<CaseComment[]>([]);
    const draft = ref('');
    const submitting = ref(false);

    function baseUrl(): string {
        return `/api/v1/${targetType}/${targetId()}/comments`;
    }

    async function fetch() {
        try {
            const { data } = await api.get(baseUrl());
            comments.value = (data.items ?? []) as CaseComment[];
        } catch {
            comments.value = [];
        }
    }

    async function submit() {
        const body = draft.value.trim();
        if (!body) return;
        submitting.value = true;
        try {
            await api.post(baseUrl(), { body });
            draft.value = '';
            await fetch();
        } finally {
            submitting.value = false;
        }
    }

    function seed(initial: CaseComment[] | undefined) {
        comments.value = initial ?? [];
    }

    return { comments, draft, submitting, fetch, submit, seed };
}
