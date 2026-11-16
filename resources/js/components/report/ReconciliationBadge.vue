<script setup lang="ts">
import { CircleCheck, CircleDashed, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import { formatUsd, isZero } from '@/lib/money';
import { cn } from '@/lib/utils';

const props = defineProps<{
    difference: string | null;
}>();

const state = computed(() => {
    if (props.difference === null) {
        return {
            icon: CircleDashed,
            label: 'No billing data',
            tone: 'bg-muted text-muted-foreground',
        };
    }

    if (isZero(props.difference)) {
        return {
            icon: CircleCheck,
            label: 'Reconciled',
            tone: 'bg-accent text-accent-foreground',
        };
    }

    return {
        icon: TriangleAlert,
        label: `Off by ${formatUsd(props.difference)}`,
        tone: 'bg-destructive/10 text-destructive',
    };
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium whitespace-nowrap',
                state.tone,
            )
        "
    >
        <component :is="state.icon" class="size-3 shrink-0" />
        {{ state.label }}
    </span>
</template>
