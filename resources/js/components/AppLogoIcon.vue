<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 32 32"
        fill="none"
        stroke="currentColor"
        stroke-width="2.2"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="className"
        v-bind="$attrs"
    >
        <path d="M4 14.5 16 5l12 9.5" />
        <path d="M7 12.5V27h18V12.5" />
        <path d="M11 27v-6.5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2V27" />
        <path d="M11 22.5h10" />
    </svg>
</template>
