<script setup lang="ts">
import { computed } from 'vue';
import { formatUsd } from '@/lib/money';

const props = defineProps<{
    value: string | null | undefined;
    decimals?: number;
}>();

const formatted = computed(() => formatUsd(props.value, props.decimals));
const exact = computed(() =>
    props.value ? `$${props.value} exactly` : undefined,
);
</script>

<template>
    <span class="tabular-nums" :title="exact">{{ formatted }}</span>
</template>
