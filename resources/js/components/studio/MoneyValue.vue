<script setup lang="ts">
import { computed } from 'vue';
import { formatUsd } from '@/lib/money';

const props = withDefaults(
    defineProps<{
        value: string | null | undefined;
        decimals?: number;
    }>(),
    { decimals: 4 },
);

const formatted = computed(() => formatUsd(props.value, props.decimals));
const exact = computed(() =>
    props.value ? `$${props.value} exactly` : undefined,
);
</script>

<template>
    <span class="font-mono tabular-nums" :title="exact">{{ formatted }}</span>
</template>
