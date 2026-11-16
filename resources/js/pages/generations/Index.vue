<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DesignForm from '@/components/studio/design-form/DesignForm.vue';
import GenerationCardSkeleton from '@/components/studio/GenerationCardSkeleton.vue';
import GenerationFeed from '@/components/studio/GenerationFeed.vue';
import StatsStrip from '@/components/studio/StatsStrip.vue';
import { useGenerationFeed } from '@/composables/useGenerationFeed';
import type {
    Generation,
    GenerationStats,
    Pricing,
    ResourceCollection,
} from '@/types/generation';

const props = defineProps<{
    generations?: ResourceCollection<Generation>;
    watched?: ResourceCollection<Generation>;
    stats?: GenerationStats;
    pricing?: Pricing;
    markup?: string;
    maxReferenceImages?: number;
}>();

const { items, polling } = useGenerationFeed(
    () => props.generations,
    () => props.watched,
);
</script>

<template>
    <Head title="Studio" />

    <header class="space-y-8 border-b px-4 pt-12 pb-10 md:px-10">
        <div class="max-w-2xl space-y-3">
            <p class="text-sm font-medium text-primary">Studio</p>
            <h1
                class="text-4xl leading-[1.05] font-medium tracking-tight text-balance text-ink md:text-5xl"
            >
                What room are we designing today?
            </h1>
            <p class="font-light text-muted-foreground">
                Describe it on the left. Your designs appear on the right and
                update live while they render.
            </p>
        </div>
        <StatsStrip :stats="stats" />
    </header>

    <div class="grid flex-1 lg:grid-cols-[440px_1fr]">
        <section
            class="border-b px-4 py-8 md:px-10 lg:border-r lg:border-b-0"
            aria-label="New design"
        >
            <div class="lg:sticky lg:top-24">
                <DesignForm
                    :pricing="pricing"
                    :markup="markup ?? '1.25'"
                    :max-reference-images="maxReferenceImages ?? 4"
                />
            </div>
        </section>

        <section
            class="min-w-0 bg-sand/60 px-4 py-8 md:px-8"
            aria-label="Your designs"
        >
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-sm font-medium text-ink">Your designs</h2>
                <span
                    v-if="polling"
                    class="inline-flex items-center gap-2 text-xs text-muted-foreground"
                >
                    <span
                        class="size-1.5 animate-pulse-dot rounded-full bg-primary text-primary"
                    />
                    Updating live
                </span>
            </div>
            <GenerationFeed v-if="generations" :items="items" />
            <div v-else class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
                <GenerationCardSkeleton v-for="skeleton in 4" :key="skeleton" />
            </div>
        </section>
    </div>
</template>
