import { useNow as useVueUseNow } from '@vueuse/core';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { elapsedSeconds, relativeTime } from '@/lib/time';

const now = useVueUseNow({ interval: 1000 });

export function useNow(): ComputedRef<number> {
    return computed(() => now.value.getTime());
}

export function useRelativeTime(iso: () => string | null): ComputedRef<string> {
    const current = useNow();

    return computed(() => relativeTime(iso(), current.value));
}

export function useElapsed(iso: () => string | null): ComputedRef<number> {
    const current = useNow();

    return computed(() => elapsedSeconds(iso(), current.value));
}
