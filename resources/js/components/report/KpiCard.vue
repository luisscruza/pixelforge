<script setup lang="ts">
defineProps<{
    label: string;
}>();
</script>

<template>
    <div class="space-y-2 border-r border-b bg-white px-5 py-4">
        <p class="text-sm text-muted-foreground">{{ label }}</p>
        <p class="text-2xl font-medium tracking-tight text-ink tabular-nums">
            <slot />
        </p>
        <div v-if="$slots.footer" class="text-xs text-muted-foreground">
            <slot name="footer" />
        </div>
    </div>
</template>
