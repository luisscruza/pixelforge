<script setup lang="ts">
import { CircleCheck, CircleDashed, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import { formatUsd, isZero } from '@/lib/money';
import { cn } from '@/lib/utils';

const props = defineProps<{
    difference: string | null;
    compact?: boolean;
}>();

const state = computed(() => {
    if (props.difference === null) {
        return {
            icon: CircleDashed,
            label: 'No usage data',
            tone: 'text-muted-foreground',
        };
    }

    if (isZero(props.difference)) {
        return {
            icon: CircleCheck,
            label: 'Matches Pixelforge',
            tone: 'text-success',
        };
    }

    return {
        icon: TriangleAlert,
        label: `Off by ${formatUsd(props.difference, 6)}`,
        tone: 'text-destructive',
    };
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-1 font-mono text-[11px] whitespace-nowrap',
                state.tone,
            )
        "
        :title="state.label"
    >
        <component :is="state.icon" class="size-3.5 shrink-0" />
        <span v-if="!compact">{{ state.label }}</span>
    </span>
</template>
