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
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div
                    v-for="tile in 4"
                    :key="tile"
                    class="h-[68px] skeleton-shimmer rounded-lg"
                />
            </div>
        </template>

        <div v-if="stats" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
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
                ><MoneyValue :value="stats.spent" :decimals="2"
            /></StatTile>
        </div>
    </Deferred>
</template>
