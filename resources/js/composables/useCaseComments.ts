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

    async function fetch() {
        try {
            const { data } = await api.get('/api/v1/comments', {
                params: { target_type: targetType, target_id: targetId() },
            });
            comments.value = data.items ?? [];
        } catch {
            comments.value = [];
        }
    }

    async function submit() {
        const body = draft.value.trim();
        if (!body) return;
        submitting.value = true;
        try {
            await api.post('/api/v1/comments', { target_type: targetType, target_id: targetId(), body });
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
