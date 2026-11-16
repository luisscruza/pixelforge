<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';
import MoneyValue from '@/components/studio/MoneyValue.vue';
import StatTile from '@/components/studio/StatTile.vue';
import type { GenerationStats } from '@/types/generation';

defineProps<{
    stats?: GenerationStats;
}>();
</script>

<template>
    <Deferred data="stats">
        <template #fallback>
            <div class="grid grid-cols-2 border-t border-l sm:grid-cols-4">
                <div
                    v-for="tile in 4"
                    :key="tile"
                    class="space-y-2.5 border-r border-b bg-white px-5 py-4"
                >
                    <div class="h-3 w-20 skeleton-shimmer" />
                    <div class="h-6 w-12 skeleton-shimmer" />
                </div>
            </div>
        </template>

        <div
            v-if="stats"
            class="grid grid-cols-2 border-t border-l sm:grid-cols-4"
        >
            <StatTile label="Rooms designed">{{ stats.succeeded }}</StatTile>
            <StatTile label="In the studio">
                <span class="inline-flex items-center gap-2">
                    {{ stats.in_flight }}
                    <span
                        v-if="stats.in_flight > 0"
                        class="size-2 animate-pulse-dot rounded-full bg-primary text-primary"
                    />
                </span>
            </StatTile>
            <StatTile label="Total requests">{{ stats.total }}</StatTile>
            <StatTile label="Spent"
                ><MoneyValue :value="stats.spent"
            /></StatTile>
        </div>
    </Deferred>
</template>
