<script setup lang="ts">
import { computed } from 'vue';
import { useElapsed } from '@/composables/useNow';
import { formatDuration } from '@/lib/time';
import type { Generation } from '@/types/generation';

const props = defineProps<{
    generation: Generation;
}>();

const elapsed = useElapsed(() => props.generation.created_at);

const caption = computed(() => {
    if (props.generation.optimistic) {
        return 'Sending to the studio…';
    }

    if (props.generation.is_confirming) {
        return 'Confirming your request…';
    }

    return props.generation.status === 'processing'
        ? 'Drafting your room…'
        : 'Waiting for a free drafting table…';
});
</script>

<template>
    <div class="absolute inset-0 overflow-hidden bg-blueprint">
        <div
            class="absolute inset-x-0 top-0 h-[10%] animate-scan bg-gradient-to-b from-transparent via-primary/25 to-transparent"
        />
        <svg
            class="absolute inset-0 size-full text-primary/35"
            viewBox="0 0 120 80"
            preserveAspectRatio="none"
            fill="none"
            stroke="currentColor"
            stroke-width="0.4"
        >
            <path
                d="M14 66 L14 22 L60 10 L106 22 L106 66 Z"
                stroke-dasharray="2 2"
            />
            <path d="M14 66 L106 66" />
            <path d="M44 66 L44 44 L60 44 L60 66" />
            <rect
                x="72"
                y="34"
                width="20"
                height="14"
                stroke-dasharray="1.5 1.5"
            />
        </svg>
        <div
            class="absolute inset-x-3 bottom-3 flex items-center justify-between font-mono text-[11px] text-primary/80"
        >
            <span>{{ caption }}</span>
            <span class="tabular-nums">{{ formatDuration(elapsed) }}</span>
        </div>
    </div>
</template>
