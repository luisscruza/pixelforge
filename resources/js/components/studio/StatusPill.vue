<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { GenerationStatus } from '@/types/generation';

const props = defineProps<{
    status: GenerationStatus;
    confirming?: boolean;
}>();

const STYLES: Record<
    GenerationStatus,
    { label: string; tone: string; dot: string }
> = {
    queued: {
        label: 'Queued',
        tone: 'bg-muted text-muted-foreground',
        dot: 'bg-muted-foreground animate-pulse-dot',
    },
    processing: {
        label: 'Rendering',
        tone: 'bg-accent text-accent-foreground',
        dot: 'bg-primary animate-pulse-dot',
    },
    succeeded: {
        label: 'Ready',
        tone: 'bg-success/12 text-success',
        dot: 'bg-success',
    },
    failed: {
        label: 'Failed',
        tone: 'bg-destructive/10 text-destructive',
        dot: 'bg-destructive',
    },
};

const style = computed(() => STYLES[props.status]);
const label = computed(() =>
    props.confirming ? 'Confirming' : style.value.label,
);
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 font-mono text-[11px] font-medium tracking-wide uppercase transition-colors duration-300',
                style.tone,
            )
        "
    >
        <span :class="cn('size-1.5 rounded-full text-current', style.dot)" />
        {{ label }}
    </span>
</template>
