<script setup lang="ts">
import { InfiniteScroll } from '@inertiajs/vue3';
import { ref } from 'vue';
import EmptyStudio from '@/components/studio/EmptyStudio.vue';
import GenerationCard from '@/components/studio/GenerationCard.vue';
import GenerationCardSkeleton from '@/components/studio/GenerationCardSkeleton.vue';
import GenerationLightbox from '@/components/studio/GenerationLightbox.vue';
import type { Generation } from '@/types/generation';

defineProps<{
    items: Generation[];
}>();

const selected = ref<Generation | null>(null);
const lightboxOpen = ref(false);

function open(generation: Generation): void {
    selected.value = generation;
    lightboxOpen.value = true;
}
</script>

<template>
    <EmptyStudio v-if="items.length === 0" />

    <InfiniteScroll
        v-else
        data="generations"
        only-next
        :buffer="400"
        items-element="#generation-grid"
    >
        <TransitionGroup
            id="generation-grid"
            tag="div"
            class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3"
            move-class="transition duration-500"
        >
            <GenerationCard
                v-for="generation in items"
                :key="generation.id"
                :generation="generation"
                @open="open"
            />
        </TransitionGroup>

        <template #loading>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
                <GenerationCardSkeleton v-for="skeleton in 3" :key="skeleton" />
            </div>
        </template>
    </InfiniteScroll>

    <GenerationLightbox v-model:open="lightboxOpen" :generation="selected" />
</template>
