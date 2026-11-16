<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DesignForm from '@/components/studio/design-form/DesignForm.vue';
import GenerationCardSkeleton from '@/components/studio/GenerationCardSkeleton.vue';
import GenerationFeed from '@/components/studio/GenerationFeed.vue';
import StatsStrip from '@/components/studio/StatsStrip.vue';
import { useGenerationFeed } from '@/composables/useGenerationFeed';
import { index } from '@/routes/generations';
import type {
    Generation,
    GenerationStats,
    Pricing,
    ResourceCollection,
} from '@/types/generation';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Studio', href: index() }],
    },
});

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

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p
                    class="font-mono text-[11px] tracking-[0.2em] text-primary uppercase"
                >
                    InteriorIA · Studio
                </p>
                <h1
                    class="text-2xl font-semibold tracking-tight text-balance md:text-3xl"
                >
                    Design a room in one prompt
                </h1>
            </div>
            <span
                v-if="polling"
                class="inline-flex items-center gap-2 font-mono text-[11px] text-muted-foreground"
            >
                <span
                    class="size-1.5 animate-pulse-dot rounded-full bg-primary text-primary"
                />
                Live
            </span>
        </header>

        <StatsStrip :stats="stats" />

        <div
            class="grid items-start gap-6 lg:grid-cols-[minmax(340px,400px)_1fr]"
        >
            <section
                class="rounded-xl border bg-card p-5 shadow-xs lg:sticky lg:top-4"
                aria-label="New design"
            >
                <DesignForm
                    :pricing="pricing"
                    :markup="markup ?? '1.25'"
                    :max-reference-images="maxReferenceImages ?? 4"
                />
            </section>

            <section aria-label="Your designs" class="min-w-0">
                <GenerationFeed v-if="generations" :items="items" />
                <div v-else class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
                    <GenerationCardSkeleton
                        v-for="skeleton in 6"
                        :key="skeleton"
                    />
                </div>
            </section>
        </div>
    </div>
</template>
