<script setup lang="ts">
defineProps<{
    label: string;
}>();
</script>

<template>
    <div class="rounded-lg border bg-card px-4 py-3">
        <p
            class="font-mono text-[10px] tracking-wider text-muted-foreground uppercase"
        >
            {{ label }}
        </p>
        <p class="mt-1 text-xl font-semibold tabular-nums"><slot /></p>
    </div>
</template>
