<script setup lang="ts">
import { FlaskConical } from '@lucide/vue';
import { PROVIDER_SIMULATIONS } from '@/lib/studio';

defineEmits<{
    insert: [keyword: string];
}>();
</script>

<template>
    <details class="group rounded-md border border-dashed px-3 py-2 text-xs">
        <summary
            class="flex cursor-pointer list-none items-center gap-1.5 text-muted-foreground select-none hover:text-foreground"
        >
            <FlaskConical class="size-3.5" />
            Simulate provider behaviour
            <span
                class="ml-auto font-mono text-[10px] transition group-open:rotate-90"
                >›</span
            >
        </summary>
        <p class="mt-2 text-muted-foreground">
            Adds a Pixelforge test keyword to the prompt, so you can watch each
            failure path play out.
        </p>
        <div class="mt-2 flex flex-wrap gap-1.5">
            <button
                v-for="simulation in PROVIDER_SIMULATIONS"
                :key="simulation.keyword"
                type="button"
                class="rounded border bg-card px-2 py-1 transition hover:border-primary/50 active:scale-95"
                @click="$emit('insert', simulation.keyword)"
            >
                <span class="font-mono text-primary">{{
                    simulation.keyword
                }}</span>
                <span class="ml-1 text-muted-foreground">{{
                    simulation.label
                }}</span>
            </button>
        </div>
    </details>
</template>
