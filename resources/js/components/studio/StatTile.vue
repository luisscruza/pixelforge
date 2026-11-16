<script setup lang="ts">
defineProps<{
    label: string;
}>();
</script>

<template>
    <div class="border-r border-b bg-white px-5 py-4">
        <p class="text-xs text-muted-foreground">{{ label }}</p>
        <p
            class="mt-1 text-2xl font-medium tracking-tight text-ink tabular-nums"
        >
            <slot />
        </p>
    </div>
</template>
