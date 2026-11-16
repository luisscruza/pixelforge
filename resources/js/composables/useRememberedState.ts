import { useRemember } from '@inertiajs/vue3';
import { isRef, reactive } from 'vue';

export function useRememberedState<T extends object>(
    initial: T,
    key: string,
): T {
    const remembered = useRemember(reactive(initial) as T, key);

    return isRef(remembered) ? remembered.value : remembered;
}
