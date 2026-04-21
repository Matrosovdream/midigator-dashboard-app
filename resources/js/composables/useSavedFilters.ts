import { watch, type Ref } from 'vue';

const STORAGE_PREFIX = 'mg.savedFilter.';

export function useSavedFilters<T extends Record<string, any>>(key: string, state: Ref<T>) {
    const storageKey = STORAGE_PREFIX + key;

    const raw = localStorage.getItem(storageKey);
    if (raw) {
        try {
            Object.assign(state.value, JSON.parse(raw));
        } catch {
            // ignore
        }
    }

    watch(
        state,
        (v) => {
            try {
                localStorage.setItem(storageKey, JSON.stringify(v));
            } catch {
                // ignore quota errors
            }
        },
        { deep: true },
    );

    function clear() {
        localStorage.removeItem(storageKey);
    }

    return { clear };
}
