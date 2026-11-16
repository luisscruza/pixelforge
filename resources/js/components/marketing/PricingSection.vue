<script setup lang="ts">
import { CircleCheck } from '@lucide/vue';
import SectionIntro from '@/components/marketing/SectionIntro.vue';

const PROMISES = [
    'See an estimate and a hard maximum before you submit',
    'We hold the maximum, then charge only the actual cost',
    'Failed renders are always free',
    'Buy credits in small packs, no subscription',
];

const RECEIPT = [
    { label: 'Prompt', detail: '6 tokens', value: '0.000030' },
    {
        label: 'Render, wide · Showroom',
        detail: '6,450 tokens',
        value: '0.258000',
    },
    { label: 'Design reasoning', detail: '3,184 tokens', value: '0.039800' },
    { label: 'Inspiration photo', detail: '765 tokens', value: '0.007650' },
];
</script>

<template>
    <section class="bg-sand">
        <div
            id="pricing"
            class="mx-auto grid max-w-7xl scroll-mt-16 gap-12 border-x px-4 py-20 md:grid-cols-2 md:px-12 md:py-28"
        >
            <div class="space-y-8">
                <SectionIntro
                    eyebrow="Honest pricing"
                    title="Pay for what your room actually costs."
                >
                    Every render is priced from the work it really took, to the
                    fraction of a cent, plus a flat 25% for the studio. No
                    rounding up and no surprises.
                </SectionIntro>
                <ul class="space-y-3">
                    <li
                        v-for="promise in PROMISES"
                        :key="promise"
                        class="flex items-start gap-2.5 text-sm text-ink"
                    >
                        <CircleCheck
                            class="mt-0.5 size-4 shrink-0 text-primary"
                        />
                        {{ promise }}
                    </li>
                </ul>
            </div>

            <figure class="self-center border bg-white">
                <figcaption
                    class="flex items-center justify-between border-b px-5 py-3 text-sm"
                >
                    <span class="text-muted-foreground">A real receipt</span>
                    <span class="font-medium text-primary"
                        >Wide · Showroom</span
                    >
                </figcaption>
                <dl class="divide-y text-sm">
                    <div
                        v-for="line in RECEIPT"
                        :key="line.label"
                        class="grid grid-cols-[1fr_auto] gap-x-4 px-5 py-3"
                    >
                        <dt class="text-ink">{{ line.label }}</dt>
                        <dd class="font-mono tabular-nums">
                            ${{ line.value }}
                        </dd>
                        <dd class="text-xs text-muted-foreground">
                            {{ line.detail }}
                        </dd>
                    </div>
                    <div class="grid grid-cols-[1fr_auto] px-5 py-3">
                        <dt class="text-muted-foreground">Studio fee (25%)</dt>
                        <dd class="font-mono tabular-nums">$0.076370</dd>
                    </div>
                    <div
                        class="grid grid-cols-[1fr_auto] bg-accent px-5 py-3 font-medium"
                    >
                        <dt>You pay</dt>
                        <dd class="font-mono tabular-nums">$0.381850</dd>
                    </div>
                </dl>
            </figure>
        </div>
    </section>
</template>
