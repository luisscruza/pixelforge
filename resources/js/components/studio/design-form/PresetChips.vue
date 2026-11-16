<script setup lang="ts">
defineProps<{
    label: string;
    presets: readonly string[];
}>();

defineEmits<{
    pick: [preset: string];
}>();
</script>

<template>
    <div class="flex flex-wrap items-center gap-1.5">
        <span
            class="mr-1 font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
            >{{ label }}</span
        >
        <button
            v-for="preset in presets"
            :key="preset"
            type="button"
            class="rounded-full border bg-card px-2.5 py-0.5 text-xs text-muted-foreground transition hover:border-primary/50 hover:text-foreground active:scale-95"
            @click="$emit('pick', preset)"
        >
            {{ preset }}
        </button>
    </div>
</template>
