<script setup lang="ts">
import { LayoutPanelTop, PenLine, Sparkles } from '@lucide/vue';
import SectionIntro from '@/components/marketing/SectionIntro.vue';

const STEPS = [
    {
        number: '01',
        icon: PenLine,
        title: 'Describe the room',
        body: 'Write it the way you would brief a designer, or start from a room and a style preset. Add up to four inspiration photos if you have a mood board.',
    },
    {
        number: '02',
        icon: LayoutPanelTop,
        title: 'Pick a format and finish',
        body: 'Wide for a whole room, tall for walls and stairs. Sketch for a fast concept, Showroom for the finest detail. The estimate updates as you type.',
    },
    {
        number: '03',
        icon: Sparkles,
        title: 'Watch it come together',
        body: 'Your design appears on the drafting table and updates live. When it is ready you see exactly what it cost. If it fails, it is free.',
    },
];
</script>

<template>
    <section id="how-it-works" class="mx-auto max-w-7xl scroll-mt-16 border-x">
        <div class="px-4 py-20 md:px-12 md:py-28">
            <SectionIntro
                eyebrow="How it works"
                title="From a sentence to a styled room."
            >
                No design software, no back and forth. Three decisions and a
                short wait.
            </SectionIntro>

            <div class="mt-14 grid border-t border-l md:grid-cols-3">
                <article
                    v-for="step in STEPS"
                    :key="step.number"
                    class="group flex flex-col gap-10 border-r border-b p-6 transition-colors hover:bg-sand"
                >
                    <div class="flex items-start justify-between">
                        <span
                            class="grid size-9 place-items-center bg-accent text-primary transition group-hover:bg-primary group-hover:text-white"
                        >
                            <component :is="step.icon" class="size-4" />
                        </span>
                        <span class="font-mono text-xs text-muted-foreground">
                            {{ step.number }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-xl font-medium text-ink">
                            {{ step.title }}
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-light text-muted-foreground"
                        >
                            {{ step.body }}
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
