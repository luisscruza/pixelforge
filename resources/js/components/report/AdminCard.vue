<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <section class="border bg-white">
        <header
            class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-3.5"
        >
            <div>
                <h2 class="text-sm font-medium text-ink">{{ title }}</h2>
                <p v-if="description" class="text-xs text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <slot name="actions" />
        </header>
        <slot />
    </section>
</template>
