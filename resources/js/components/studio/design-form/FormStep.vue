<script setup lang="ts">
defineProps<{
    number: number;
    title: string;
    optional?: boolean;
}>();
</script>

<template>
    <section class="space-y-3">
        <header class="flex items-center gap-2.5">
            <span
                class="grid size-5 place-items-center bg-ink font-mono text-[10px] text-white"
            >
                {{ number }}
            </span>
            <h3 class="text-sm font-medium text-ink">{{ title }}</h3>
            <span v-if="optional" class="text-xs text-muted-foreground">
                optional
            </span>
        </header>
        <slot />
    </section>
</template>
