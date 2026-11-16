import { usePoll } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { ComputedRef } from 'vue';
import type { Generation, ResourceCollection } from '@/types/generation';

const POLL_INTERVAL_MS = 2500;

export function useGenerationFeed(
    generations: () => ResourceCollection<Generation> | undefined,
    watched: () => ResourceCollection<Generation> | undefined,
): {
    items: ComputedRef<Generation[]>;
    activeIds: ComputedRef<string[]>;
    polling: ComputedRef<boolean>;
} {
    const latest = computed(
        () =>
            new Map(
                (watched()?.data ?? []).map((generation) => [
                    generation.id,
                    generation,
                ]),
            ),
    );

    const items = computed(() =>
        (generations()?.data ?? []).map(
            (generation) => latest.value.get(generation.id) ?? generation,
        ),
    );

    const activeIds = computed(() =>
        items.value
            .filter(
                (generation) => generation.is_active && !generation.optimistic,
            )
            .map((generation) => generation.id),
    );

    const poll = usePoll(
        POLL_INTERVAL_MS,
        () => ({
            only: ['watched', 'credits', 'stats'],
            data: { watch: activeIds.value },
            preserveUrl: true,
        }),
        { autoStart: false, mode: 'rest', background: 'pause' },
    );

    watch(
        () => activeIds.value.length > 0,
        (hasActive) => (hasActive ? poll.start() : poll.stop()),
        { immediate: true },
    );

    return { items, activeIds, polling: computed(() => poll.polling.value) };
}
